import { Plugin } from 'vue';
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
export type MosaicastListener = (payload: Record<string, unknown>, source: MosaicastEventSource) => void;
/**
 * Receives Mosaicast events from Inertia responses and private broadcasts.
 */
export declare class MosaicastClient {
    private readonly listeners;
    on(name: string, listener: MosaicastListener): () => void;
    off(name: string, listener: MosaicastListener): void;
    /** @internal */
    emit(name: string, payload: Record<string, unknown>, source: MosaicastEventSource): void;
    private normalizeName;
    /** @return string[] */
    private listenerNames;
}
/**
 * Get the application's shared Mosaicast client.
 */
export declare function mosaicast(): MosaicastClient;
export interface MosaicastPluginOptions {
    channelPrefix?: string;
    debug?: boolean;
    echo?: MosaicastEcho;
    logger?: Pick<Console, 'error' | 'info'>;
}
/**
 * Creates the initial Mosaicast Vue plugin.
 *
 * Diagnostics are disabled by default and can be enabled with `debug`.
 */
export declare function createMosaicast(options?: MosaicastPluginOptions): Plugin;
//# sourceMappingURL=index.d.ts.map