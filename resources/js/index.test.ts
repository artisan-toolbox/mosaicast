import { describe, expect, it, vi } from 'vite-plus/test';

const { navigate } = vi.hoisted(() => ({
    navigate: vi.fn(),
}));

vi.mock('@inertiajs/vue3', () => ({
    router: {
        on: navigate,
    },
}));

import { createMosaicast, mosaicast } from './index';

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

        expect(navigate).toHaveBeenCalledWith('navigate', expect.any(Function));

        const onNavigate = navigate.mock.calls[0][1] as (event: {
            detail: { page: { props: { mosaicast: unknown } } };
        }) => void;

        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier:
                                '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                            events: [{ name: 'orders.updated', payload: { orderId: 123 } }],
                        },
                    },
                },
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

        expect(listener).toHaveBeenCalledWith({ orderId: 456 }, 'broadcast');

        stop();
        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier:
                                '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                            events: [{ name: 'orders.updated', payload: { orderId: 789 } }],
                        },
                    },
                },
            },
        });

        expect(logger.info).toHaveBeenCalledWith('[Mosaicast] Inertia event:', {
            event: { name: 'orders.updated', payload: { orderId: 789 } },
            source: 'Inertia response',
        });
        expect(listener).toHaveBeenCalledTimes(2);
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

        const onNavigate = navigate.mock.calls.at(-1)?.[1] as (event: {
            detail: { page: { props: { mosaicast: unknown } } };
        }) => void;

        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier:
                                '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                            events: [{ name: 'orders.updated', payload: { orderId: 123 } }],
                        },
                    },
                },
            },
        });

        expect(logger.info).not.toHaveBeenCalled();
        expect(logger.error).not.toHaveBeenCalled();
    });

    it('ignores malformed Inertia payloads', () => {
        const logger = { error: vi.fn(), info: vi.fn() };

        createMosaicast({ debug: true, logger }).install?.({} as never);

        const onNavigate = navigate.mock.calls.at(-1)?.[1] as (event: {
            detail: { page: { props: { mosaicast: unknown } } };
        }) => void;

        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier: 'not-a-session-identifier',
                            events: [],
                        },
                    },
                },
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

        const onNavigate = navigate.mock.calls.at(-1)?.[1] as (event: {
            detail: { page: { props: { mosaicast: unknown } } };
        }) => void;

        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier:
                                '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                            events: [],
                        },
                    },
                },
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

        const onNavigate = navigate.mock.calls.at(-1)?.[1] as (event: {
            detail: { page: { props: { mosaicast: unknown } } };
        }) => void;

        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier:
                                '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                            events: [],
                        },
                    },
                },
            },
        });
        onNavigate({
            detail: {
                page: { props: { mosaicast: { sessionIdentifier: null, events: [] } } },
            },
        });

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

        const onNavigate = navigate.mock.calls.at(-1)?.[1] as (event: {
            detail: { page: { props: { mosaicast: unknown } } };
        }) => void;
        const navigateTo = (sessionIdentifier: string): void =>
            onNavigate({
                detail: {
                    page: { props: { mosaicast: { sessionIdentifier, events: [] } } },
                },
            });

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

        const onNavigate = navigate.mock.calls.at(-1)?.[1] as (event: {
            detail: { page: { props: Record<string, unknown> } };
        }) => void;

        onNavigate({
            detail: {
                page: {
                    props: {
                        mosaicast: {
                            sessionIdentifier:
                                '9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
                            events: [],
                        },
                    },
                },
            },
        });
        onNavigate({
            detail: {
                page: {
                    props: {
                        orders: [],
                        mosaicastSessionIdentifier:
                            '84367bb1439cbe1e5018b7075eb748e952038ce1cee777ce4d20b0f60f657558',
                    },
                },
            },
        });

        expect(echo.leave).toHaveBeenCalledWith(
            'mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681',
        );
        expect(echo.private).toHaveBeenCalledWith(
            'mosaicast.sessions.84367bb1439cbe1e5018b7075eb748e952038ce1cee777ce4d20b0f60f657558',
        );
    });
});
