import { router } from '@inertiajs/vue3';
import type { Plugin } from 'vue';

export interface MosaicastEvent {
    name: string;
    payload: Record<string, unknown>;
}

export interface MosaicastPayload {
    sessionIdentifier: string | null;
    events: MosaicastEvent[];
}

export interface MosaicastPrivateChannel {
    error(callback: (error: unknown) => void): this;
    listenToAll(callback: (eventName: string, event: unknown) => void): this;
    subscribed(callback: () => void): this;
}

export interface MosaicastEcho {
    leave(channel: string): void;
    private(channel: string): MosaicastPrivateChannel;
}

export type MosaicastEventSource = 'broadcast' | 'inertia';

export type MosaicastListener = (
    payload: Record<string, unknown>,
    source: MosaicastEventSource,
) => void;

/**
 * Receives Mosaicast events from Inertia responses and private broadcasts.
 */
export class MosaicastClient {
    private readonly listeners = new Map<string, Set<MosaicastListener>>();

    public on(name: string, listener: MosaicastListener): () => void {
        const eventName = this.normalizeName(name);
        const listeners = this.listeners.get(eventName) ?? new Set<MosaicastListener>();

        listeners.add(listener);
        this.listeners.set(eventName, listeners);

        return (): void => this.off(eventName, listener);
    }

    public off(name: string, listener: MosaicastListener): void {
        const eventName = this.normalizeName(name);
        const listeners = this.listeners.get(eventName);

        if (listeners === undefined) {
            return;
        }

        listeners.delete(listener);

        if (listeners.size === 0) {
            this.listeners.delete(eventName);
        }
    }

    /** @internal */
    public emit(
        name: string,
        payload: Record<string, unknown>,
        source: MosaicastEventSource,
    ): void {
        const listeners = new Set<MosaicastListener>();

        for (const eventName of this.listenerNames(name)) {
            for (const listener of this.listeners.get(eventName) ?? []) {
                listeners.add(listener);
            }
        }

        for (const listener of listeners) {
            listener(payload, source);
        }
    }

    private normalizeName(name: string): string {
        return name.startsWith('.') ? name.substring(1) : name;
    }

    /** @return string[] */
    private listenerNames(name: string): string[] {
        const eventName = this.normalizeName(name);
        const appEventsNamespace = 'App\\Events\\';

        if (!eventName.startsWith(appEventsNamespace)) {
            return [eventName];
        }

        const separator = eventName.lastIndexOf('\\');

        return [eventName, eventName.substring(separator + 1)];
    }
}

const client = new MosaicastClient();

/**
 * Get the application's shared Mosaicast client.
 */
export function mosaicast(): MosaicastClient {
    return client;
}

export interface MosaicastPluginOptions {
    channelPrefix?: string;
    debug?: boolean;
    echo?: MosaicastEcho;
    logger?: Pick<Console, 'error' | 'info'>;
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function eventPayload(value: unknown): Record<string, unknown> | undefined {
    if (isRecord(value)) {
        return value;
    }

    if (Array.isArray(value) && value.length === 0) {
        return {};
    }
}

function isSessionIdentifier(value: unknown): value is string {
    return typeof value === 'string' && /^[a-f0-9]{64}$/.test(value);
}

function isMosaicastPayload(
    value: unknown,
): value is { sessionIdentifier: string | null; events: unknown[] } {
    if (!isRecord(value) || !Array.isArray(value.events)) {
        return false;
    }

    return value.sessionIdentifier === null || isSessionIdentifier(value.sessionIdentifier);
}

/**
 * Creates the initial Mosaicast Vue plugin.
 *
 * Diagnostics are disabled by default and can be enabled with `debug`.
 */
export function createMosaicast(options: MosaicastPluginOptions = {}): Plugin {
    return {
        install(): void {
            const echo = options.echo;
            const channelPrefix = options.channelPrefix ?? 'mosaicast.sessions';
            const logger =
                options.debug === true ? (options.logger ?? globalThis.console) : undefined;
            const mosaicastClient = mosaicast();
            let subscribedChannel: string | null = null;

            const synchronizeSession = (sessionIdentifier: string | null): void => {
                if (sessionIdentifier === null && subscribedChannel !== null) {
                    echo?.leave(subscribedChannel);
                    subscribedChannel = null;
                }

                if (sessionIdentifier !== null) {
                    logger?.info('[Mosaicast] Session identifier:', sessionIdentifier);

                    const channel = `${channelPrefix}.${sessionIdentifier}`;

                    if (echo === undefined) {
                        logger?.error(
                            '[Mosaicast] No Echo instance was provided; private channel subscription was skipped.',
                        );
                    } else if (subscribedChannel !== channel) {
                        if (subscribedChannel !== null) {
                            echo.leave(subscribedChannel);
                        }

                        logger?.info('[Mosaicast] Subscribing to private channel:', channel);

                        echo.private(channel)
                            .subscribed((): void => {
                                logger?.info('[Mosaicast] Private channel subscribed:', channel);
                            })
                            .error((error: unknown): void => {
                                logger?.error('[Mosaicast] Private channel subscription failed:', {
                                    channel,
                                    error,
                                });
                            })
                            .listenToAll((eventName: string, event: unknown): void => {
                                if (subscribedChannel !== channel) {
                                    return;
                                }

                                const payload = eventPayload(event);

                                if (payload === undefined) {
                                    logger?.error(
                                        '[Mosaicast] Ignored an invalid broadcast event payload.',
                                    );

                                    return;
                                }

                                logger?.info('[Mosaicast] Broadcast event:', {
                                    event,
                                    eventName,
                                });

                                mosaicastClient.emit(eventName, payload, 'broadcast');
                            });

                        subscribedChannel = channel;
                    }
                }
            };

            const consume = (payload: unknown, sessionSynchronized: boolean): void => {
                if (payload === null || payload === undefined) {
                    return;
                }

                if (!isMosaicastPayload(payload)) {
                    logger?.error('[Mosaicast] Ignored an invalid Mosaicast payload.');

                    return;
                }

                if (!sessionSynchronized) {
                    synchronizeSession(payload.sessionIdentifier);
                }

                for (const event of payload.events) {
                    if (
                        !isRecord(event) ||
                        typeof event.name !== 'string' ||
                        event.name.length === 0
                    ) {
                        logger?.error('[Mosaicast] Ignored an invalid Mosaicast event.');
                        continue;
                    }

                    const data = eventPayload(event.payload);

                    if (data === undefined) {
                        logger?.error('[Mosaicast] Ignored an invalid Mosaicast event.');
                        continue;
                    }

                    logger?.info('[Mosaicast] Inertia event:', {
                        event,
                        source: 'Inertia response',
                    });

                    mosaicastClient.emit(event.name, data, 'inertia');
                }
            };

            const consumePage = (props: Record<string, unknown>): void => {
                const sessionIdentifier = props.mosaicastSessionIdentifier;
                const sessionSynchronized =
                    sessionIdentifier === null || isSessionIdentifier(sessionIdentifier);

                if (sessionSynchronized) {
                    synchronizeSession(sessionIdentifier);
                } else if (sessionIdentifier !== undefined) {
                    logger?.error('[Mosaicast] Ignored an invalid session identifier.');
                }

                if (props.mosaicastHasEvents !== false) {
                    consume(props.mosaicast, sessionSynchronized);
                }
            };

            let initialPage = true;
            let pendingProps: Record<string, unknown> | undefined;

            router.on('navigate', ({ detail }): void => {
                if (initialPage) {
                    initialPage = false;
                    const navigation = globalThis.performance?.getEntriesByType('navigation')[0];

                    if (
                        navigation &&
                        'type' in navigation &&
                        navigation.type === 'back_forward' &&
                        detail.page.flash?.mosaicastInitialPage !== true
                    ) {
                        return;
                    }

                    consumePage(detail.page.props);
                }
            });

            router.on('beforeUpdate', ({ detail }): void => {
                initialPage = false;
                pendingProps = detail.page.props;
            });

            const consumeResponse = (): void => {
                const props = pendingProps;
                pendingProps = undefined;

                if (props !== undefined) {
                    consumePage(props);
                }
            };

            router.on('success', consumeResponse);
            router.on('error', consumeResponse);
        },
    };
}
