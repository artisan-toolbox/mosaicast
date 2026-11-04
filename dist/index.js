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
	if (i(e)) return e;
	if (Array.isArray(e) && e.length === 0) return {};
}
function o(e) {
	return typeof e == "string" && /^[a-f0-9]{64}$/.test(e);
}
function s(e) {
	return !i(e) || !Array.isArray(e.events) ? !1 : e.sessionIdentifier === null || o(e.sessionIdentifier);
}
function c(t = {}) {
	return { install() {
		let n = t.echo, c = t.channelPrefix ?? "mosaicast.sessions", l = t.debug === !0 ? t.logger ?? globalThis.console : void 0, u = r(), d = null, f = (e) => {
			if (e === null && d !== null && (n?.leave(d), d = null), e !== null) {
				l?.info("[Mosaicast] Session identifier:", e);
				let t = `${c}.${e}`;
				n === void 0 ? l?.error("[Mosaicast] No Echo instance was provided; private channel subscription was skipped.") : d !== t && (d !== null && n.leave(d), l?.info("[Mosaicast] Subscribing to private channel:", t), n.private(t).subscribed(() => {
					l?.info("[Mosaicast] Private channel subscribed:", t);
				}).error((e) => {
					l?.error("[Mosaicast] Private channel subscription failed:", {
						channel: t,
						error: e
					});
				}).listenToAll((e, n) => {
					if (d !== t) return;
					let r = a(n);
					if (r === void 0) {
						l?.error("[Mosaicast] Ignored an invalid broadcast event payload.");
						return;
					}
					l?.info("[Mosaicast] Broadcast event:", {
						event: n,
						eventName: e
					}), u.emit(e, r, "broadcast");
				}), d = t);
			}
		}, p = (e, t) => {
			if (e != null) {
				if (!s(e)) {
					l?.error("[Mosaicast] Ignored an invalid Mosaicast payload.");
					return;
				}
				t || f(e.sessionIdentifier);
				for (let t of e.events) {
					if (!i(t) || typeof t.name != "string" || t.name.length === 0) {
						l?.error("[Mosaicast] Ignored an invalid Mosaicast event.");
						continue;
					}
					let e = a(t.payload);
					if (e === void 0) {
						l?.error("[Mosaicast] Ignored an invalid Mosaicast event.");
						continue;
					}
					l?.info("[Mosaicast] Inertia event:", {
						event: t,
						source: "Inertia response"
					}), u.emit(t.name, e, "inertia");
				}
			}
		}, m = (e) => {
			let t = e.mosaicastSessionIdentifier, n = t === null || o(t);
			n ? f(t) : t !== void 0 && l?.error("[Mosaicast] Ignored an invalid session identifier."), e.mosaicastHasEvents !== !1 && p(e.mosaicast, n);
		}, h = !0, g;
		e.on("navigate", ({ detail: e }) => {
			if (h) {
				h = !1;
				let t = globalThis.performance?.getEntriesByType("navigation")[0];
				if (t && "type" in t && t.type === "back_forward" && e.page.flash?.mosaicastInitialPage !== !0) return;
				m(e.page.props);
			}
		}), e.on("beforeUpdate", ({ detail: e }) => {
			h = !1, g = e.page.props;
		});
		let _ = () => {
			let e = g;
			g = void 0, e !== void 0 && m(e);
		};
		e.on("success", _), e.on("error", _);
	} };
}
//#endregion
export { t as MosaicastClient, c as createMosaicast, r as mosaicast };
