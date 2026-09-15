import { router as e } from "@inertiajs/vue3";
//#region resources/js/index.ts
var t = class {
	listeners = /* @__PURE__ */ new Map();
	on(e, t) {
		let n = this.normalizeName(e), r = this.listeners.get(n) ?? /* @__PURE__ */ new Set();
		return r.add(t), this.listeners.set(n, r), () => this.off(n, t);
	}
	off(e, t) {
		let n = this.normalizeName(e), r = this.listeners.get(n);
		r !== void 0 && (r.delete(t), r.size === 0 && this.listeners.delete(n));
	}
	emit(e, t, n) {
		let r = /* @__PURE__ */ new Set();
		for (let t of this.listenerNames(e)) for (let e of this.listeners.get(t) ?? []) r.add(e);
		for (let e of r) e(t, n);
	}
	normalizeName(e) {
		return e.startsWith(".") ? e.substring(1) : e;
	}
	listenerNames(e) {
		let t = this.normalizeName(e);
		if (!t.startsWith("App\\Events\\")) return [t];
		let n = t.lastIndexOf("\\");
		return [t, t.substring(n + 1)];
	}
}, n = new t();
function r() {
	return n;
}
function i(e) {
	return typeof e == "object" && !!e && !Array.isArray(e);
}
function a(e) {
	return i(e) && typeof e.name == "string" && e.name.length > 0 && i(e.payload);
}
function o(e) {
	return typeof e == "string" && /^[a-f0-9]{64}$/.test(e);
}
function s(e) {
	return !i(e) || !Array.isArray(e.events) ? !1 : (e.sessionIdentifier === null || o(e.sessionIdentifier)) && e.events.every(a);
}
function c(t = {}) {
	return { install() {
		let n = t.echo, a = t.channelPrefix ?? "mosaicast.sessions", c = t.debug === !0 ? t.logger ?? globalThis.console : void 0, l = r(), u = null, d = (e) => {
			if (e === null && u !== null && (n?.leave(u), u = null), e !== null) {
				c?.info("[Mosaicast] Session identifier:", e);
				let t = `${a}.${e}`;
				n === void 0 ? c?.error("[Mosaicast] No Echo instance was provided; private channel subscription was skipped.") : u !== t && (u !== null && n.leave(u), c?.info("[Mosaicast] Subscribing to private channel:", t), n.private(t).subscribed(() => {
					c?.info("[Mosaicast] Private channel subscribed:", t);
				}).error((e) => {
					c?.error("[Mosaicast] Private channel subscription failed:", {
						channel: t,
						error: e
					});
				}).listenToAll((e, n) => {
					if (u === t) {
						if (!i(n)) {
							c?.error("[Mosaicast] Ignored an invalid broadcast event payload.");
							return;
						}
						c?.info("[Mosaicast] Broadcast event:", {
							event: n,
							eventName: e
						}), l.emit(e, n, "broadcast");
					}
				}), u = t);
			}
		}, f = (e, t) => {
			if (e != null) {
				if (!s(e)) {
					c?.error("[Mosaicast] Ignored an invalid Mosaicast payload.");
					return;
				}
				t || d(e.sessionIdentifier);
				for (let t of e.events) c?.info("[Mosaicast] Inertia event:", {
					event: t,
					source: "Inertia response"
				}), l.emit(t.name, t.payload, "inertia");
			}
		};
		e.on("navigate", (e) => {
			let t = e.detail.page.props, n = t.mosaicastSessionIdentifier, r = n === null || o(n);
			r ? d(n) : n !== void 0 && c?.error("[Mosaicast] Ignored an invalid session identifier."), f(t.mosaicast, r);
		});
	} };
}
//#endregion
export { t as MosaicastClient, c as createMosaicast, r as mosaicast };
