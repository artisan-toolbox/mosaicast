import { describe, expect, it, vi } from 'vite-plus/test';

const { on } = vi.hoisted(() => ({ on: vi.fn() }));

vi.mock('@inertiajs/vue3', () => ({ router: { on } }));

import { createMosaicast, mosaicast } from './index';

function pageEvent(
    name: string,
    props: Record<string, unknown>,
    flash: Record<string, unknown> = {},
): void {
    const call = on.mock.calls.filter(([event]) => event === name).at(-1);
    call?.[1]({ detail: { page: { props, flash } } });
}

function response(props: Record<string, unknown>, result = 'success'): void {
    pageEvent('beforeUpdate', props);
    pageEvent(result, props);
}

describe('createMosaicast', () => {
    it('processes the initial Inertia navigation and subscribes to its private session channel', () => {
        let onBroadcast: ((eventName: string, event: unknown) => void) | undefined;
        const subscription = {
            error: vi.fn(),
            listenToAll: vi.fn((callback: (eventName: string, event: unknown) => void) => {
                onBroadcast = callback;

                return subscription;
            }),
            subscribed: vi.fn(),
        };

        subscription.error.mockReturnValue(subscription);
        subscription.subscribed.mockReturnValue(subscription);

        const echo = {
            leave: vi.fn(),
            private: vi.fn(() => subscription),
        };
        const logger = { error: vi.fn(), info: vi.fn() };
        const listener = vi.fn();
        const stop = mosaicast().on('.orders.updated', listener);

        createMosaicast({
            debug: true,
            echo,
            logger,
        }).install?.({} as never);

        pageEvent('navigate', {
            mosaicast: {
                sessionIdentifier:
                    '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                events: [{ name: 'orders.updated', payload: { orderId: 123 } }],
            },
        });

        expect(echo.private).toHaveBeenCalledWith(
            'mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
        expect(subscription.subscribed).toHaveBeenCalledOnce();
        expect(subscription.error).toHaveBeenCalledOnce();
        expect(subscription.listenToAll).toHaveBeenCalledOnce();
        expect(logger.info).toHaveBeenCalledWith(
            '[Mosaicast] Session identifier:',
            '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
        expect(logger.info).toHaveBeenCalledWith('[Mosaicast] Inertia event:', {
            event: { name: 'orders.updated', payload: { orderId: 123 } },
            source: 'Inertia response',
        });
        expect(logger.info).toHaveBeenCalledWith(
            '[Mosaicast] Subscribing to private channel:',
            'mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
        expect(listener).toHaveBeenCalledWith({ orderId: 123 }, 'inertia');

        onBroadcast?.('.orders.updated', { orderId: 456 });
        onBroadcast?.('.orders.updated', []);

        expect(listener).toHaveBeenCalledWith({ orderId: 456 }, 'broadcast');
        expect(listener).toHaveBeenCalledWith({}, 'broadcast');

        stop();
        response({
            mosaicast: {
                sessionIdentifier:
                    '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                events: [{ name: 'orders.updated', payload: { orderId: 789 } }],
            },
        });

        expect(logger.info).toHaveBeenCalledWith('[Mosaicast] Inertia event:', {
            event: { name: 'orders.updated', payload: { orderId: 789 } },
            source: 'Inertia response',
        });
        expect(listener).toHaveBeenCalledTimes(3);
    });

    it('delivers empty payloads and valid siblings when a response replaces the page', () => {
        const listener = vi.fn();
        const stop = mosaicast().on('orders.updated', listener);
        createMosaicast().install?.({} as never);

        response(
            {
                mosaicastHasEvents: true,
                mosaicast: {
                    sessionIdentifier: null,
                    events: [
                        { name: 'orders.updated', payload: [] },
                        { name: 'orders.updated', payload: null },
                        { name: 'orders.updated', payload: { orderId: 123 } },
                    ],
                },
            },
            'error',
        );

        expect(listener.mock.calls).toEqual([
            [{}, 'inertia'],
            [{ orderId: 123 }, 'inertia'],
        ]);
        stop();
    });

    it('ignores retained event props on a partial response and history navigation', () => {
        const listener = vi.fn();
        const stop = mosaicast().on('orders.updated', listener);
        createMosaicast().install?.({} as never);
        const props = {
            mosaicastHasEvents: true,
            mosaicast: {
                sessionIdentifier: null,
                events: [{ name: 'orders.updated', payload: { orderId: 123 } }],
            },
        };

        pageEvent('navigate', props);
        response({ ...props, mosaicastHasEvents: false });
        pageEvent('navigate', structuredClone(props));

        expect(listener).toHaveBeenCalledExactlyOnceWith({ orderId: 123 }, 'inertia');
        stop();
    });

    it('delivers a fresh document on back/forward without replaying restored history', () => {
        const listener = vi.fn();
        const stop = mosaicast().on('orders.updated', listener);
        const props = {
            mosaicastHasEvents: true,
            mosaicast: {
                sessionIdentifier: null,
                events: [{ name: 'orders.updated', payload: { orderId: 123 } }],
            },
        };
        vi.stubGlobal('performance', { getEntriesByType: () => [{ type: 'back_forward' }] });

        try {
            createMosaicast().install?.({} as never);
            pageEvent('navigate', props);
            expect(listener).not.toHaveBeenCalled();

            createMosaicast().install?.({} as never);
            pageEvent('navigate', props, { mosaicastInitialPage: true });
            expect(listener).toHaveBeenCalledExactlyOnceWith({ orderId: 123 }, 'inertia');
        } finally {
            vi.unstubAllGlobals();
            stop();
        }
    });

    it('removes a listener with off()', () => {
        const listener = vi.fn();

        mosaicast().on('App\\Events\\EventA', listener);
        mosaicast().off('.App\\Events\\EventA', listener);
        mosaicast().emit('App\\Events\\EventA', { orderId: 123 }, 'inertia');

        expect(listener).not.toHaveBeenCalled();
    });

    it('matches a short listener name to a namespaced Laravel event', () => {
        const listener = vi.fn();
        const stop = mosaicast().on('EventA', listener);

        mosaicast().emit('App\\Events\\EventA', { orderId: 123 }, 'inertia');

        expect(listener).toHaveBeenCalledWith({ orderId: 123 }, 'inertia');

        stop();
    });

    it('does not match unrelated event namespaces by their short class name', () => {
        const listener = vi.fn();
        const stop = mosaicast().on('EventA', listener);

        mosaicast().emit('Other\\Events\\EventA', { orderId: 123 }, 'inertia');

        expect(listener).not.toHaveBeenCalled();

        stop();
    });

    it('does not log diagnostics unless debug is enabled', () => {
        const logger = { error: vi.fn(), info: vi.fn() };

        createMosaicast({ logger }).install?.({} as never);

        pageEvent('navigate', {
            mosaicast: {
                sessionIdentifier:
                    '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                events: [{ name: 'orders.updated', payload: { orderId: 123 } }],
            },
        });

        expect(logger.info).not.toHaveBeenCalled();
        expect(logger.error).not.toHaveBeenCalled();
    });

    it('ignores malformed Inertia payloads', () => {
        const logger = { error: vi.fn(), info: vi.fn() };

        createMosaicast({ debug: true, logger }).install?.({} as never);

        pageEvent('navigate', {
            mosaicast: {
                sessionIdentifier: 'not-a-session-identifier',
                events: [],
            },
        });

        expect(logger.error).toHaveBeenCalledWith(
            '[Mosaicast] Ignored an invalid Mosaicast payload.',
        );
    });

    it('uses the configured channel prefix', () => {
        const echo = {
            leave: vi.fn(),
            private: vi.fn(() => ({
                subscribed: vi.fn().mockReturnThis(),
                error: vi.fn().mockReturnThis(),
                listenToAll: vi.fn().mockReturnThis(),
            })),
        };

        createMosaicast({ echo, channelPrefix: 'custom.sessions' }).install?.({} as never);

        pageEvent('navigate', {
            mosaicast: {
                sessionIdentifier:
                    '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                events: [],
            },
        });

        expect(echo.private).toHaveBeenCalledWith(
            'custom.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
    });

    it('leaves a stale private channel when a later page has no session', () => {
        const echo = {
            leave: vi.fn(),
            private: vi.fn(() => ({
                subscribed: vi.fn().mockReturnThis(),
                error: vi.fn().mockReturnThis(),
                listenToAll: vi.fn().mockReturnThis(),
            })),
        };

        createMosaicast({ echo }).install?.({} as never);

        pageEvent('navigate', {
            mosaicast: {
                sessionIdentifier:
                    '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                events: [],
            },
        });
        response({ mosaicast: { sessionIdentifier: null, events: [] } });

        expect(echo.leave).toHaveBeenCalledWith(
            'mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
    });

    it('ignores callbacks from a channel after the session changes', () => {
        const callbacks: Array<(name: string, payload: unknown) => void> = [];
        const echo = {
            leave: vi.fn(),
            private: vi.fn(() => ({
                subscribed: vi.fn().mockReturnThis(),
                error: vi.fn().mockReturnThis(),
                listenToAll: vi.fn((callback: (name: string, payload: unknown) => void) => {
                    callbacks.push(callback);

                    return { subscribed: vi.fn(), error: vi.fn(), listenToAll: vi.fn() };
                }),
            })),
        };
        const listener = vi.fn();
        const stop = mosaicast().on('orders.updated', listener);

        createMosaicast({ echo }).install?.({} as never);

        const navigateTo = (sessionIdentifier: string): void =>
            response({ mosaicast: { sessionIdentifier, events: [] } });

        navigateTo('9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681');
        navigateTo('84367bb1439cbe1e5018b7075eb748e952038ce1cee777ce4d20b0f60f657558');

        callbacks[0]?.('orders.updated', { orderId: 123 });
        callbacks[1]?.('orders.updated', { orderId: 456 });

        expect(listener).toHaveBeenCalledOnce();
        expect(listener).toHaveBeenCalledWith({ orderId: 456 }, 'broadcast');

        stop();
    });

    it('switches channels on a partial response that omits Mosaicast events', () => {
        const echo = {
            leave: vi.fn(),
            private: vi.fn(() => ({
                subscribed: vi.fn().mockReturnThis(),
                error: vi.fn().mockReturnThis(),
                listenToAll: vi.fn().mockReturnThis(),
            })),
        };

        createMosaicast({ echo }).install?.({} as never);

        pageEvent('navigate', {
            mosaicast: {
                sessionIdentifier:
                    '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                events: [],
            },
        });
        response({
            orders: [],
            mosaicastSessionIdentifier:
                '84367bb1439cbe1e5018b7075eb748e952038ce1cee777ce4d20b0f60f657558',
        });

        expect(echo.leave).toHaveBeenCalledWith(
            'mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
        expect(echo.private).toHaveBeenCalledWith(
            'mosaicast.sessions.84367bb1439cbe1e5018b7075eb748e952038ce1cee777ce4d20b0f60f657558',
        );
    });
});
