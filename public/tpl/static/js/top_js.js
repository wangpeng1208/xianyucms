function checkcookie() {
	return document.cookie.indexOf("mb_u=") >= 0 ? (islogin = 1, !0) : !1
}
function PlayHistoryClass() {
	var a, b, c, d;
	this.getPlayArray = function() {
		var e, f, g, h, i;
		if (a = document.cookie, e = a.indexOf("xianyu_vod_v=") + "xianyu_vod_v=".length, f = a.indexOf("_$_|", e), g = unescape(a.substring(e, f)), -1 == f) return g = "", void 0;
		for (d = g.split("_$_"), b = new Array, c = new Array, h = 0; h < d.length; h++) i = d[h].split("^"), b[h] = i[0], c[h] = i[1]
	}, this.viewPlayHistory = function(a) {
		var f, g, h, i, d = document.getElementById(a);
		if (checkcookie() && ($.get(Root + "index.php?s=User-Comm-getplaylog", function(a) {
			var b, c;
			if (a["rcode"] > -1) if ($("#his-todo").hide(), null != a["r"] && a["r"].length > 0) {
				f = "";
				for (b in a["r"]) c = a["r"][b], f += '<li><h5><a href="' + c["vod_readurl"] + '">' + c["vod_name"] + "</a><em>/</em><a href='" + c["vod_palyurl"] + "' target='_blank'>" + c["url_name"] + '</a></h5><label><a class="color" href="' + c["vod_palyurl"] + "\">继续观看</a></label><a href=\"javascript:;\" target='_blank' class='delck' data=\"" + c["id"] + "\" mtype='ajax'>删除</a></li>";
				f.length > 0 && ($("#emptybt").unbind(), $("#emptybt").click(function() {
					return PlayHistoryObj.emptyhistory("ajax"), !1
				}), d.innerHTML = f, $(".delck").click(function() {
					return PlayHistoryObj.removeHistory($(this).attr("data"), $(this).attr("mtype")), $(this).parent("li").remove(), !1
				}))
			} else document.getElementById("playhistory").innerHTML = "<li class='no-his'><p>暂无播放历史列表...</p></li>"
		}, "json"), $("#his-todo").hide()), navigator.cookieEnabled) {
			if (f = "", void 0 != b && null != b) for (g = b.length - 1; g >= 0; g--) h = b[g].split("|"), i = c[g].split("|"), 2 == h.length && 2 == i.length && (f += '<li><h5><a href="' + h[1] + '">' + h[0] + "</a><em>/</em><a target='_blank' href=\"" + i[1] + '">' + i[0] + '</a></h5><label><a class="color" target=\'_blank\' href="' + i[1] + "\">继续观看</a></label><a href=\"javascript:;\" class='delck' data='" + g + "' mtype='inck'>删除</a></li>");
			$("#his-todo").show(), $("#morelog").hide(), f.length > 0 ? ($("#emptybt").unbind(), $("#emptybt").click(function() {
				return PlayHistoryObj.emptyhistory("ck"), !1
			}), d.innerHTML = f, $(".delck").click(function() {
				return PlayHistoryObj.removeHistory($(this).attr("data"), $(this).attr("mtype")) ? ($(this).parent("li").remove(), !1) : void 0
			})) : document.getElementById("playhistory").innerHTML = "<li class='no-his'><p>暂无播放历史列表...</p></li>"
		} else set(d, "您浏览器关闭了cookie功能，不能为您自动保存最近浏览过的网页。")
	}, this.removeHistory = function(e, f) {
		if (this.getPlayArray(), "inck" == f) {
			if (expireTime = new Date((new Date).setDate((new Date).getDate() + 30)), timeAndPathStr = "|; expires=" + expireTime.toGMTString() + "; path=/", -1 != a.indexOf("xianyu_vod_v=") || -1 != a.indexOf("_$_|")) {
				var h = "";
				for (i in d) i != e && (h += escape(b[i]) + "^" + escape(c[i]) + "_$_");
				return document.cookie = "xianyu_vod_v=" + h + timeAndPathStr, !0
			}
			return !1
		}
		$.get(Root + "index.php?s=user-comm-delplaylog", {
			id: e
		}, function() {}, "json")
	}, this.addPlayHistory = function(e, f, g) {
		var i, j, k, l, m, n, o, p, h = 10;
		if (checkcookie() && $.post(Root + "index.php?s=user-comm-addplaylog", e), i = e.vod_name + "|" + f, j = e.url_name + "|" + g, k = escape(i) + "^", l = escape(j) + "_$_", m = new Date((new Date).setDate((new Date).getDate() + 30)), n = "|; expires=" + m.toGMTString() + "; path=/", -1 == a.indexOf("xianyu_vod_v=") && -1 == a.indexOf("_$_|") || void 0 == d) document.cookie = "xianyu_vod_v=" + k + l + n;
		else {
			if (o = "", d.length < h) for (p in d) b[p] != i && (o += escape(b[p]) + "^" + escape(c[p]) + "_$_");
			else for (p = 1; h > p; p++) b[p] != i && (o += escape(b[p]) + "^" + escape(c[p]) + "_$_");
			document.cookie = "xianyu_vod_v=" + o + k + l + n
		}
	}, this.emptyhistory = function(a) {
		return $.showfloatdiv({
			txt: "确定删除？",
			wantclose: 2,
			suredo: function() {
				return "ajax" == a ? $.get(Root + "index.php?s=user-comm-emptyhistory", function() {
					document.getElementById("playhistory").innerHTML = "<li class='no-his'><p>暂无播放历史列表...</p></li>"
				}) : _GC(), $.closefloatdiv(), !0
			}
		}), !1
	}
}
function _GC() {
	document.getElementById("playhistory").innerHTML = "<li class='no-his'><p>暂无播放历史列表...</p></li>", new Date(1970, 1, 1), document.cookie = "xianyu_vod_v=; path=/"
}
function killErrors() {
	return !0
}
function showTop(a) {
	1 == topShow ? switchTab("top", a, 2, "history") : (document.getElementById("Tab_top_" + a).className = "history", document.getElementById("List_top_" + a).style.display = "", topShow = !1)
}
function hideTop() {
	var a, b;
	for (i = 0; 2 > i; i++) a = document.getElementById("Tab_top_" + i), b = document.getElementById("List_top_" + i), a.className = "history", b.style.display = "none"
}
function checkcookie() {
	return document.cookie.indexOf("auth=") >= 0 ? (islogin = 1, !0) : !1
}
function mathRand(a, b) {
	var f, c = 0,
		e = [];
	for (f = 0; a > f; f++) c = Math.floor(Math.random() * b), -1 == $.inArray(c, e) ? e.push(c) : f--;
	return e
}
function setTab(a, b, c) {
	var d, e;
	for (i = 1; c >= i; i++) d = document.getElementById(a + i), e = document.getElementById("con_" + a + "_" + i), d.className = i == b ? "current" : "", e.style.display = i == b ? "block" : "none"
}
function qrsearch() {
	return "请在此处输入影片片名或演员名称。" == $("#xdwwd").val() || "" == $("#wd").val() ? ($("#xdwwd").val(""), $("#xdwwd").focus()) : document.location = Root + "index.php?s=vod-search-wd-" + encodeURIComponent($("#wd").val()) + ".html", !1
}
function intval(a) {
	return a = parseInt(a), isNaN(a) ? 0 : a
}
function getPos(a) {
	for (var b = 0, c = 0, d = intval(a.style.width), e = intval(a.style.height), f = a.offsetWidth, g = a.offsetHeight; a.offsetParent;) b += a.offsetLeft + (a.currentStyle ? intval(a.currentStyle.borderLeftWidth) : 0), c += a.offsetTop + (a.currentStyle ? intval(a.currentStyle.borderTopWidth) : 0), a = a.offsetParent;
	return b += a.offsetLeft + (a.currentStyle ? intval(a.currentStyle.borderLeftWidth) : 0), c += a.offsetTop + (a.currentStyle ? intval(a.currentStyle.borderTopWidth) : 0), {
		x: b,
		y: c,
		w: d,
		h: e,
		wb: f,
		hb: g
	}
}
function getScroll() {
	var a, b, c, d;
	return document.documentElement && document.documentElement.scrollTop ? (a = document.documentElement.scrollTop, b = document.documentElement.scrollLeft, c = document.documentElement.scrollWidth, d = document.documentElement.scrollHeight) : document.body && (a = document.body.scrollTop, b = document.body.scrollLeft, c = document.body.scrollWidth, d = document.body.scrollHeight), {
		t: a,
		l: b,
		w: c,
		h: d
	}
}
function scroller(a, b) {
	if ("object" != typeof a && (a = document.getElementById(a)), a) {
		var c = this;
		c.el = a, c.p = getPos(a), c.s = getScroll(), c.clear = function() {
			window.clearInterval(c.timer), c.timer = null
		}, c.t = (new Date).getTime(), c.step = function() {
			var a = (new Date).getTime(),
				d = (a - c.t) / b;
			a >= b + c.t ? (c.clear(), window.setTimeout(function() {
				c.scroll(c.p.y, c.p.x)
			}, 13)) : (st = (-Math.cos(d * Math.PI) / 2 + .5) * (c.p.y - c.s.t) + c.s.t, sl = (-Math.cos(d * Math.PI) / 2 + .5) * (c.p.x - c.s.l) + c.s.l, c.scroll(st, sl))
		}, c.scroll = function(a, b) {
			window.scrollTo(b, a)
		}, c.timer = window.setInterval(function() {
			c.step()
		}, 13)
	}
}
function SetHome(a, b) {
	try {
		a.style.behavior = "url(#default#homepage)", a.setHomePage(b)
	} catch (c) {
		if (window.netscape) {
			try {
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect")
			} catch (c) {
				alert("温馨提示:\n浏览器不允许网页设置首页。\n请手动进入浏览器选项设置主页。")
			}
			var d = Components.classes["@mozilla.org/preferences-service;1"].getService(Components.interfaces.nsIPrefBranch);
			d.setCharPref("browser.startup.homepage", b)
		}
	}
}
var PlayHistoryObj, topShow, FF, lazyloadImg;
!
function(a, b) {
	function c(a) {
		return J.isWindow(a) ? a : 9 === a.nodeType ? a.defaultView || a.parentWindow : !1
	}
	function d(a) {
		if (!oc[a]) {
			var b = G.body,
				c = J("<" + a + ">").appendTo(b),
				d = c.css("display");
			c.remove(), ("none" === d || "" === d) && (pc || (pc = G.createElement("iframe"), pc.frameBorder = pc.width = pc.height = 0), b.appendChild(pc), qc && pc.createElement || (qc = (pc.contentWindow || pc.contentDocument).document, qc.write((J.support.boxModel ? "<!doctype html>" : "") + "<html><body>"), qc.close()), c = qc.createElement(a), qc.body.appendChild(c), d = J.css(c, "display"), b.removeChild(pc)), oc[a] = d
		}
		return oc[a]
	}
	function e(a, b) {
		var c = {};
		return J.each(uc.concat.apply([], uc.slice(0, b)), function() {
			c[this] = a
		}), c
	}
	function f() {
		vc = b
	}
	function g() {
		return setTimeout(f, 0), vc = J.now()
	}
	function h() {
		try {
			return new a.ActiveXObject("Microsoft.XMLHTTP")
		} catch (b) {}
	}
	function i() {
		try {
			return new a.XMLHttpRequest
		} catch (b) {}
	}
	function j(a, c) {
		a.dataFilter && (c = a.dataFilter(c, a.dataType));
		var f, g, i, k, l, m, n, o, d = a.dataTypes,
			e = {},
			h = d.length,
			j = d[0];
		for (f = 1; h > f; f++) {
			if (1 === f) for (g in a.converters)"string" == typeof g && (e[g.toLowerCase()] = a.converters[g]);
			if (k = j, j = d[f], "*" === j) j = k;
			else if ("*" !== k && k !== j) {
				if (l = k + " " + j, m = e[l] || e["* " + j], !m) {
					o = b;
					for (n in e) if (i = n.split(" "), (i[0] === k || "*" === i[0]) && (o = e[i[1] + " " + j])) {
						n = e[n], n === !0 ? m = o : o === !0 && (m = n);
						break
					}
				}!m && !o && J.error("No conversion from " + l.replace(" ", " to ")), m !== !0 && (c = m ? m(c) : o(n(c)))
			}
		}
		return c
	}
	function k(a, c, d) {
		var h, i, j, k, e = a.contents,
			f = a.dataTypes,
			g = a.responseFields;
		for (i in g) i in d && (c[g[i]] = d[i]);
		for (;
		"*" === f[0];) f.shift(), h === b && (h = a.mimeType || c.getResponseHeader("content-type"));
		if (h) for (i in e) if (e[i] && e[i].test(h)) {
			f.unshift(i);
			break
		}
		if (f[0] in d) j = f[0];
		else {
			for (i in d) {
				if (!f[0] || a.converters[i + " " + f[0]]) {
					j = i;
					break
				}
				k || (k = i)
			}
			j = j || k
		}
		return j ? (j !== f[0] && f.unshift(j), d[j]) : void 0
	}
	function l(a, b, c, d) {
		if (J.isArray(b)) J.each(b, function(b, e) {
			c || Qb.test(a) ? d(a, e) : l(a + "[" + ("object" == typeof e ? b : "") + "]", e, c, d)
		});
		else if (c || "object" !== J.type(b)) d(a, b);
		else for (var e in b) l(a + "[" + e + "]", b[e], c, d)
	}
	function m(a, c) {
		var d, e, f = J.ajaxSettings.flatOptions || {};
		for (d in c) c[d] !== b && ((f[d] ? a : e || (e = {}))[d] = c[d]);
		e && J.extend(!0, a, e)
	}
	function n(a, c, d, e, f, g) {
		f = f || c.dataTypes[0], g = g || {}, g[f] = !0;
		for (var l, h = a[f], i = 0, j = h ? h.length : 0, k = a === dc; j > i && (k || !l); i++) l = h[i](c, d, e), "string" == typeof l && (!k || g[l] ? l = b : (c.dataTypes.unshift(l), l = n(a, c, d, e, l, g)));
		return (k || !l) && !g["*"] && (l = n(a, c, d, e, "*", g)), l
	}
	function o(a) {
		return function(b, c) {
			if ("string" != typeof b && (c = b, b = "*"), J.isFunction(c)) for (var g, h, i, d = b.toLowerCase().split(_b), e = 0, f = d.length; f > e; e++) g = d[e], i = /^\+/.test(g), i && (g = g.substr(1) || "*"), h = a[g] = a[g] || [], h[i ? "unshift" : "push"](c)
		}
	}
	function p(a, b, c) {
		var d = "width" === b ? a.offsetWidth : a.offsetHeight,
			e = "width" === b ? 1 : 0,
			f = 4;
		if (d > 0) {
			if ("border" !== c) for (; f > e; e += 2) c || (d -= parseFloat(J.css(a, "padding" + Lb[e])) || 0), "margin" === c ? d += parseFloat(J.css(a, c + Lb[e])) || 0 : d -= parseFloat(J.css(a, "border" + Lb[e] + "Width")) || 0;
			return d + "px"
		}
		if (d = Mb(a, b), (0 > d || null == d) && (d = a.style[b]), Hb.test(d)) return d;
		if (d = parseFloat(d) || 0, c) for (; f > e; e += 2) d += parseFloat(J.css(a, "padding" + Lb[e])) || 0, "padding" !== c && (d += parseFloat(J.css(a, "border" + Lb[e] + "Width")) || 0), "margin" === c && (d += parseFloat(J.css(a, c + Lb[e])) || 0);
		return d + "px"
	}
	function q(a) {
		var b = G.createElement("div");
		return Cb.appendChild(b), b.innerHTML = a.outerHTML, b.firstChild
	}
	function r(a) {
		var b = (a.nodeName || "").toLowerCase();
		"input" === b ? s(a) : "script" !== b && "undefined" != typeof a.getElementsByTagName && J.grep(a.getElementsByTagName("input"), s)
	}
	function s(a) {
		("checkbox" === a.type || "radio" === a.type) && (a.defaultChecked = a.checked)
	}
	function t(a) {
		return "undefined" != typeof a.getElementsByTagName ? a.getElementsByTagName("*") : "undefined" != typeof a.querySelectorAll ? a.querySelectorAll("*") : []
	}
	function u(a, b) {
		var c;
		1 === b.nodeType && (b.clearAttributes && b.clearAttributes(), b.mergeAttributes && b.mergeAttributes(a), c = b.nodeName.toLowerCase(), "object" === c ? b.outerHTML = a.outerHTML : "input" !== c || "checkbox" !== a.type && "radio" !== a.type ? "option" === c ? b.selected = a.defaultSelected : "input" === c || "textarea" === c ? b.defaultValue = a.defaultValue : "script" === c && b.text !== a.text && (b.text = a.text) : (a.checked && (b.defaultChecked = b.checked = a.checked), b.value !== a.value && (b.value = a.value)), b.removeAttribute(J.expando), b.removeAttribute("_submit_attached"), b.removeAttribute("_change_attached"))
	}
	function v(a, b) {
		if (1 === b.nodeType && J.hasData(a)) {
			var c, d, e, f = J._data(a),
				g = J._data(b, f),
				h = f.events;
			if (h) {
				delete g.handle, g.events = {};
				for (c in h) for (d = 0, e = h[c].length; e > d; d++) J.event.add(b, c, h[c][d])
			}
			g.data && (g.data = J.extend({}, g.data))
		}
	}
	function w(a) {
		return J.nodeName(a, "table") ? a.getElementsByTagName("tbody")[0] || a.appendChild(a.ownerDocument.createElement("tbody")) : a
	}
	function x(a) {
		var b = ob.split("|"),
			c = a.createDocumentFragment();
		if (c.createElement) for (; b.length;) c.createElement(b.pop());
		return c
	}
	function y(a, b, c) {
		if (b = b || 0, J.isFunction(b)) return J.grep(a, function(a, d) {
			var e = !! b.call(a, d, a);
			return e === c
		});
		if (b.nodeType) return J.grep(a, function(a) {
			return a === b === c
		});
		if ("string" == typeof b) {
			var d = J.grep(a, function(a) {
				return 1 === a.nodeType
			});
			if (kb.test(b)) return J.filter(b, d, !c);
			b = J.filter(b, d)
		}
		return J.grep(a, function(a) {
			return J.inArray(a, b) >= 0 === c
		})
	}
	function z(a) {
		return !a || !a.parentNode || 11 === a.parentNode.nodeType
	}
	function A() {
		return !0
	}
	function B() {
		return !1
	}
	function C(a, b, c) {
		var d = b + "defer",
			e = b + "queue",
			f = b + "mark",
			g = J._data(a, d);
		!(!g || "queue" !== c && J._data(a, e) || "mark" !== c && J._data(a, f) || !setTimeout(function() {
			!J._data(a, e) && !J._data(a, f) && (J.removeData(a, d, !0), g.fire())
		}, 0))
	}
	function D(a) {
		for (var b in a) if (("data" !== b || !J.isEmptyObject(a[b])) && "toJSON" !== b) return !1;
		return !0
	}
	function E(a, c, d) {
		if (d === b && 1 === a.nodeType) {
			var e = "data-" + c.replace(N, "-$1").toLowerCase();
			if (d = a.getAttribute(e), "string" == typeof d) {
				try {
					d = "true" === d ? !0 : "false" === d ? !1 : "null" === d ? null : J.isNumeric(d) ? +d : M.test(d) ? J.parseJSON(d) : d
				} catch (f) {}
				J.data(a, c, d)
			} else d = b
		}
		return d
	}
	function F(a) {
		var c, d, b = K[a] = {};
		for (a = a.split(/\s+/), c = 0, d = a.length; d > c; c++) b[a[c]] = !0;
		return b
	}
	var L, M, N, W, X, Y, O, P, Q, R, S, T, U, V, Z, $, _, ab, bb, cb, db, eb, fb, gb, hb, ib, jb, kb, lb, mb, nb, ob, pb, qb, rb, sb, tb, ub, vb, wb, xb, yb, zb, Ab, Bb, Cb, Mb, Nb, Ob, Db, Eb, Fb, Gb, Hb, Ib, Jb, Kb, Lb, fc, gc, Pb, Qb, Rb, Sb, Tb, Ub, Vb, Wb, Xb, Yb, Zb, $b, _b, ac, bc, cc, dc, ec, hc, jc, kc, nc, lc, mc, pc, qc, tc, vc, oc, rc, sc, uc, wc, xc, yc, G = a.document,
		H = a.navigator,
		I = a.location,
		J = function() {
			function c() {
				if (!d.isReady) {
					try {
						G.documentElement.doScroll("left")
					} catch (a) {
						return setTimeout(c, 1), void 0
					}
					d.ready()
				}
			}
			var g, y, z, A, d = function(a, b) {
					return new d.fn.init(a, b, g)
				},
				e = a.jQuery,
				f = a.$,
				h = /^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,
				i = /\S/,
				j = /^\s+/,
				k = /\s+$/,
				l = /^<(\w+)\s*\/?>(?:<\/\1>)?$/,
				m = /^[\],:{}\s]*$/,
				n = /\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,
				o = /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
				p = /(?:^|:|,)(?:\s*\[)+/g,
				q = /(webkit)[ \/]([\w.]+)/,
				r = /(opera)(?:.*version)?[ \/]([\w.]+)/,
				s = /(msie) ([\w.]+)/,
				t = /(mozilla)(?:.*? rv:([\w.]+))?/,
				u = /-([a-z]|[0-9])/gi,
				v = /^-ms-/,
				w = function(a, b) {
					return (b + "").toUpperCase()
				},
				x = H.userAgent,
				B = Object.prototype.toString,
				C = Object.prototype.hasOwnProperty,
				D = Array.prototype.push,
				E = Array.prototype.slice,
				F = String.prototype.trim,
				I = Array.prototype.indexOf,
				J = {};
			return d.fn = d.prototype = {
				constructor: d,
				init: function(a, c, e) {
					var f, g, i, j;
					if (!a) return this;
					if (a.nodeType) return this.context = this[0] = a, this.length = 1, this;
					if ("body" === a && !c && G.body) return this.context = G, this[0] = G.body, this.selector = a, this.length = 1, this;
					if ("string" == typeof a) {
						if (f = "<" !== a.charAt(0) || ">" !== a.charAt(a.length - 1) || a.length < 3 ? h.exec(a) : [null, a, null], f && (f[1] || !c)) {
							if (f[1]) return c = c instanceof d ? c[0] : c, j = c ? c.ownerDocument || c : G, i = l.exec(a), i ? d.isPlainObject(c) ? (a = [G.createElement(i[1])], d.fn.attr.call(a, c, !0)) : a = [j.createElement(i[1])] : (i = d.buildFragment([f[1]], [j]), a = (i.cacheable ? d.clone(i.fragment) : i.fragment).childNodes), d.merge(this, a);
							if (g = G.getElementById(f[2]), g && g.parentNode) {
								if (g.id !== f[2]) return e.find(a);
								this.length = 1, this[0] = g
							}
							return this.context = G, this.selector = a, this
						}
						return !c || c.jquery ? (c || e).find(a) : this.constructor(c).find(a)
					}
					return d.isFunction(a) ? e.ready(a) : (a.selector !== b && (this.selector = a.selector, this.context = a.context), d.makeArray(a, this))
				},
				selector: "",
				jquery: "1.7.2",
				length: 0,
				size: function() {
					return this.length
				},
				toArray: function() {
					return E.call(this, 0)
				},
				get: function(a) {
					return null == a ? this.toArray() : 0 > a ? this[this.length + a] : this[a]
				},
				pushStack: function(a, b, c) {
					var e = this.constructor();
					return d.isArray(a) ? D.apply(e, a) : d.merge(e, a), e.prevObject = this, e.context = this.context, "find" === b ? e.selector = this.selector + (this.selector ? " " : "") + c : b && (e.selector = this.selector + "." + b + "(" + c + ")"), e
				},
				each: function(a, b) {
					return d.each(this, a, b)
				},
				ready: function(a) {
					return d.bindReady(), z.add(a), this
				},
				eq: function(a) {
					return a = +a, -1 === a ? this.slice(a) : this.slice(a, a + 1)
				},
				first: function() {
					return this.eq(0)
				},
				last: function() {
					return this.eq(-1)
				},
				slice: function() {
					return this.pushStack(E.apply(this, arguments), "slice", E.call(arguments).join(","))
				},
				map: function(a) {
					return this.pushStack(d.map(this, function(b, c) {
						return a.call(b, c, b)
					}))
				},
				end: function() {
					return this.prevObject || this.constructor(null)
				},
				push: D,
				sort: [].sort,
				splice: [].splice
			}, d.fn.init.prototype = d.fn, d.extend = d.fn.extend = function() {
				var a, c, e, f, g, h, i = arguments[0] || {},
					j = 1,
					k = arguments.length,
					l = !1;
				for ("boolean" == typeof i && (l = i, i = arguments[1] || {}, j = 2), "object" != typeof i && !d.isFunction(i) && (i = {}), k === j && (i = this, --j); k > j; j++) if (null != (a = arguments[j])) for (c in a) e = i[c], f = a[c], i !== f && (l && f && (d.isPlainObject(f) || (g = d.isArray(f))) ? (g ? (g = !1, h = e && d.isArray(e) ? e : []) : h = e && d.isPlainObject(e) ? e : {}, i[c] = d.extend(l, h, f)) : f !== b && (i[c] = f));
				return i
			}, d.extend({
				noConflict: function(b) {
					return a.$ === d && (a.$ = f), b && a.jQuery === d && (a.jQuery = e), d
				},
				isReady: !1,
				readyWait: 1,
				holdReady: function(a) {
					a ? d.readyWait++ : d.ready(!0)
				},
				ready: function(a) {
					if (a === !0 && !--d.readyWait || a !== !0 && !d.isReady) {
						if (!G.body) return setTimeout(d.ready, 1);
						if (d.isReady = !0, a !== !0 && --d.readyWait > 0) return;
						z.fireWith(G, [d]), d.fn.trigger && d(G).trigger("ready").off("ready")
					}
				},
				bindReady: function() {
					if (!z) {
						if (z = d.Callbacks("once memory"), "complete" === G.readyState) return setTimeout(d.ready, 1);
						if (G.addEventListener) G.addEventListener("DOMContentLoaded", A, !1), a.addEventListener("load", d.ready, !1);
						else if (G.attachEvent) {
							G.attachEvent("onreadystatechange", A), a.attachEvent("onload", d.ready);
							var b = !1;
							try {
								b = null == a.frameElement
							} catch (e) {}
							G.documentElement.doScroll && b && c()
						}
					}
				},
				isFunction: function(a) {
					return "function" === d.type(a)
				},
				isArray: Array.isArray ||
				function(a) {
					return "array" === d.type(a)
				},
				isWindow: function(a) {
					return null != a && a == a.window
				},
				isNumeric: function(a) {
					return !isNaN(parseFloat(a)) && isFinite(a)
				},
				type: function(a) {
					return null == a ? String(a) : J[B.call(a)] || "object"
				},
				isPlainObject: function(a) {
					if (!a || "object" !== d.type(a) || a.nodeType || d.isWindow(a)) return !1;
					try {
						if (a.constructor && !C.call(a, "constructor") && !C.call(a.constructor.prototype, "isPrototypeOf")) return !1
					} catch (c) {
						return !1
					}
					var e;
					for (e in a);
					return e === b || C.call(a, e)
				},
				isEmptyObject: function(a) {
					for (var b in a) return !1;
					return !0
				},
				error: function(a) {
					throw new Error(a)
				},
				parseJSON: function(b) {
					return "string" == typeof b && b ? (b = d.trim(b), a.JSON && a.JSON.parse ? a.JSON.parse(b) : m.test(b.replace(n, "@").replace(o, "]").replace(p, "")) ? new Function("return " + b)() : (d.error("Invalid JSON: " + b), void 0)) : null
				},
				parseXML: function(c) {
					if ("string" != typeof c || !c) return null;
					var e, f;
					try {
						a.DOMParser ? (f = new DOMParser, e = f.parseFromString(c, "text/xml")) : (e = new ActiveXObject("Microsoft.XMLDOM"), e.async = "false", e.loadXML(c))
					} catch (g) {
						e = b
					}
					return (!e || !e.documentElement || e.getElementsByTagName("parsererror").length) && d.error("Invalid XML: " + c), e
				},
				noop: function() {},
				globalEval: function(b) {
					b && i.test(b) && (a.execScript ||
					function(b) {
						a.eval.call(a, b)
					})(b)
				},
				camelCase: function(a) {
					return a.replace(v, "ms-").replace(u, w)
				},
				nodeName: function(a, b) {
					return a.nodeName && a.nodeName.toUpperCase() === b.toUpperCase()
				},
				each: function(a, c, e) {
					var f, g = 0,
						h = a.length,
						i = h === b || d.isFunction(a);
					if (e) if (i) {
						for (f in a) if (c.apply(a[f], e) === !1) break
					} else for (; h > g && c.apply(a[g++], e) !== !1;);
					else if (i) {
						for (f in a) if (c.call(a[f], f, a[f]) === !1) break
					} else for (; h > g && c.call(a[g], g, a[g++]) !== !1;);
					return a
				},
				trim: F ?
				function(a) {
					return null == a ? "" : F.call(a)
				} : function(a) {
					return null == a ? "" : (a + "").replace(j, "").replace(k, "")
				},
				makeArray: function(a, b) {
					var e, c = b || [];
					return null != a && (e = d.type(a), null == a.length || "string" === e || "function" === e || "regexp" === e || d.isWindow(a) ? D.call(c, a) : d.merge(c, a)), c
				},
				inArray: function(a, b, c) {
					var d;
					if (b) {
						if (I) return I.call(b, a, c);
						for (d = b.length, c = c ? 0 > c ? Math.max(0, d + c) : c : 0; d > c; c++) if (c in b && b[c] === a) return c
					}
					return -1
				},
				merge: function(a, c) {
					var f, d = a.length,
						e = 0;
					if ("number" == typeof c.length) for (f = c.length; f > e; e++) a[d++] = c[e];
					else for (; c[e] !== b;) a[d++] = c[e++];
					return a.length = d, a
				},
				grep: function(a, b, c) {
					var e, f, g, d = [];
					for (c = !! c, f = 0, g = a.length; g > f; f++) e = !! b(a[f], f), c !== e && d.push(a[f]);
					return d
				},
				map: function(a, c, e) {
					var f, g, h = [],
						i = 0,
						j = a.length,
						k = a instanceof d || j !== b && "number" == typeof j && (j > 0 && a[0] && a[j - 1] || 0 === j || d.isArray(a));
					if (k) for (; j > i; i++) f = c(a[i], i, e), null != f && (h[h.length] = f);
					else for (g in a) f = c(a[g], g, e), null != f && (h[h.length] = f);
					return h.concat.apply([], h)
				},
				guid: 1,
				proxy: function(a, c) {
					var e, f, g;
					return "string" == typeof c && (e = a[c], c = a, a = e), d.isFunction(a) ? (f = E.call(arguments, 2), g = function() {
						return a.apply(c, f.concat(E.call(arguments)))
					}, g.guid = a.guid = a.guid || g.guid || d.guid++, g) : b
				},
				access: function(a, c, e, f, g, h, i) {
					var j, k = null == e,
						l = 0,
						m = a.length;
					if (e && "object" == typeof e) {
						for (l in e) d.access(a, c, l, e[l], 1, h, f);
						g = 1
					} else if (f !== b) {
						if (j = i === b && d.isFunction(f), k && (j ? (j = c, c = function(a, b, c) {
							return j.call(d(a), c)
						}) : (c.call(a, f), c = null)), c) for (; m > l; l++) c(a[l], e, j ? f.call(a[l], l, c(a[l], e)) : f, i);
						g = 1
					}
					return g ? a : k ? c.call(a) : m ? c(a[0], e) : h
				},
				now: function() {
					return (new Date).getTime()
				},
				uaMatch: function(a) {
					a = a.toLowerCase();
					var b = q.exec(a) || r.exec(a) || s.exec(a) || a.indexOf("compatible") < 0 && t.exec(a) || [];
					return {
						browser: b[1] || "",
						version: b[2] || "0"
					}
				},
				sub: function() {
					function a(b, c) {
						return new a.fn.init(b, c)
					}
					d.extend(!0, a, this), a.superclass = this, a.fn = a.prototype = this(), a.fn.constructor = a, a.sub = this.sub, a.fn.init = function(c, e) {
						return e && e instanceof d && !(e instanceof a) && (e = a(e)), d.fn.init.call(this, c, e, b)
					}, a.fn.init.prototype = a.fn;
					var b = a(G);
					return a
				},
				browser: {}
			}), d.each("Boolean Number String Function Array Date RegExp Object".split(" "), function(a, b) {
				J["[object " + b + "]"] = b.toLowerCase()
			}), y = d.uaMatch(x), y.browser && (d.browser[y.browser] = !0, d.browser.version = y.version), d.browser.webkit && (d.browser.safari = !0), i.test(" ") && (j = /^[\s\xA0]+/, k = /[\s\xA0]+$/), g = d(G), G.addEventListener ? A = function() {
				G.removeEventListener("DOMContentLoaded", A, !1), d.ready()
			} : G.attachEvent && (A = function() {
				"complete" === G.readyState && (G.detachEvent("onreadystatechange", A), d.ready())
			}), d
		}(),
		K = {};
	J.Callbacks = function(a) {
		a = a ? K[a] || F(a) : {};
		var e, f, g, h, i, j, c = [],
			d = [],
			k = function(b) {
				var d, e, f, g;
				for (d = 0, e = b.length; e > d; d++) f = b[d], g = J.type(f), "array" === g ? k(f) : "function" === g && (!a.unique || !m.has(f)) && c.push(f)
			},
			l = function(b, k) {
				for (k = k || [], e = !a.memory || [b, k], f = !0, g = !0, j = h || 0, h = 0, i = c.length; c && i > j; j++) if (c[j].apply(b, k) === !1 && a.stopOnFalse) {
					e = !0;
					break
				}
				g = !1, c && (a.once ? e === !0 ? m.disable() : c = [] : d && d.length && (e = d.shift(), m.fireWith(e[0], e[1])))
			},
			m = {
				add: function() {
					if (c) {
						var a = c.length;
						k(arguments), g ? i = c.length : e && e !== !0 && (h = a, l(e[0], e[1]))
					}
					return this
				},
				remove: function() {
					var b, d, e, f;
					if (c) for (b = arguments, d = 0, e = b.length; e > d; d++) for (f = 0; f < c.length && (b[d] !== c[f] || (g && i >= f && (i--, j >= f && j--), c.splice(f--, 1), !a.unique)); f++);
					return this
				},
				has: function(a) {
					if (c) for (var b = 0, d = c.length; d > b; b++) if (a === c[b]) return !0;
					return !1
				},
				empty: function() {
					return c = [], this
				},
				disable: function() {
					return c = d = e = b, this
				},
				disabled: function() {
					return !c
				},
				lock: function() {
					return d = b, (!e || e === !0) && m.disable(), this
				},
				locked: function() {
					return !d
				},
				fireWith: function(b, c) {
					return d && (g ? a.once || d.push([b, c]) : (!a.once || !e) && l(b, c)), this
				},
				fire: function() {
					return m.fireWith(this, arguments), this
				},
				fired: function() {
					return !!f
				}
			};
		return m
	}, L = [].slice, J.extend({
		Deferred: function(a) {
			var i, b = J.Callbacks("once memory"),
				c = J.Callbacks("once memory"),
				d = J.Callbacks("memory"),
				e = "pending",
				f = {
					resolve: b,
					reject: c,
					notify: d
				},
				g = {
					done: b.add,
					fail: c.add,
					progress: d.add,
					state: function() {
						return e
					},
					isResolved: b.fired,
					isRejected: c.fired,
					then: function(a, b, c) {
						return h.done(a).fail(b).progress(c), this
					},
					always: function() {
						return h.done.apply(h, arguments).fail.apply(h, arguments), this
					},
					pipe: function(a, b, c) {
						return J.Deferred(function(d) {
							J.each({
								done: [a, "resolve"],
								fail: [b, "reject"],
								progress: [c, "notify"]
							}, function(a, b) {
								var f, c = b[0],
									e = b[1];
								J.isFunction(c) ? h[a](function() {
									f = c.apply(this, arguments), f && J.isFunction(f.promise) ? f.promise().then(d.resolve, d.reject, d.notify) : d[e + "With"](this === h ? d : this, [f])
								}) : h[a](d[e])
							})
						}).promise()
					},
					promise: function(a) {
						if (null == a) a = g;
						else for (var b in g) a[b] = g[b];
						return a
					}
				},
				h = g.promise({});
			for (i in f) h[i] = f[i].fire, h[i + "With"] = f[i].fireWith;
			return h.done(function() {
				e = "resolved"
			}, c.disable, d.lock).fail(function() {
				e = "rejected"
			}, b.disable, d.lock), a && a.call(h, h), h
		},
		when: function(a) {
			function b(a) {
				return function(b) {
					g[a] = arguments.length > 1 ? L.call(arguments, 0) : b, j.notifyWith(k, g)
				}
			}
			function c(a) {
				return function(b) {
					d[a] = arguments.length > 1 ? L.call(arguments, 0) : b, --h || j.resolveWith(j, d)
				}
			}
			var d = L.call(arguments, 0),
				e = 0,
				f = d.length,
				g = Array(f),
				h = f,
				j = 1 >= f && a && J.isFunction(a.promise) ? a : J.Deferred(),
				k = j.promise();
			if (f > 1) {
				for (; f > e; e++) d[e] && d[e].promise && J.isFunction(d[e].promise) ? d[e].promise().then(c(e), j.reject, b(e)) : --h;
				h || j.resolveWith(j, d)
			} else j !== a && j.resolveWith(j, f ? [a] : []);
			return k
		}
	}), J.support = function() {
		var b, c, d, e, f, g, h, i, k, l, m, n = G.createElement("div");
		if (G.documentElement, n.setAttribute("className", "t"), n.innerHTML = "   <link/><table></table><a href='/a' style='top:1px;float:left;opacity:.55;'>a</a><input type='checkbox'/>", c = n.getElementsByTagName("*"), d = n.getElementsByTagName("a")[0], !c || !c.length || !d) return {};
		e = G.createElement("select"), f = e.appendChild(G.createElement("option")), g = n.getElementsByTagName("input")[0], b = {
			leadingWhitespace: 3 === n.firstChild.nodeType,
			tbody: !n.getElementsByTagName("tbody").length,
			htmlSerialize: !! n.getElementsByTagName("link").length,
			style: /top/.test(d.getAttribute("style")),
			hrefNormalized: "/a" === d.getAttribute("href"),
			opacity: /^0.55/.test(d.style.opacity),
			cssFloat: !! d.style.cssFloat,
			checkOn: "on" === g.value,
			optSelected: f.selected,
			getSetAttribute: "t" !== n.className,
			enctype: !! G.createElement("form").enctype,
			html5Clone: "<:nav></:nav>" !== G.createElement("nav").cloneNode(!0).outerHTML,
			submitBubbles: !0,
			changeBubbles: !0,
			focusinBubbles: !1,
			deleteExpando: !0,
			noCloneEvent: !0,
			inlineBlockNeedsLayout: !1,
			shrinkWrapBlocks: !1,
			reliableMarginRight: !0,
			pixelMargin: !0
		}, J.boxModel = b.boxModel = "CSS1Compat" === G.compatMode, g.checked = !0, b.noCloneChecked = g.cloneNode(!0).checked, e.disabled = !0, b.optDisabled = !f.disabled;
		try {
			delete n.test
		} catch (p) {
			b.deleteExpando = !1
		}
		if (!n.addEventListener && n.attachEvent && n.fireEvent && (n.attachEvent("onclick", function() {
			b.noCloneEvent = !1
		}), n.cloneNode(!0).fireEvent("onclick")), g = G.createElement("input"), g.value = "t", g.setAttribute("type", "radio"), b.radioValue = "t" === g.value, g.setAttribute("checked", "checked"), g.setAttribute("name", "t"), n.appendChild(g), h = G.createDocumentFragment(), h.appendChild(n.lastChild), b.checkClone = h.cloneNode(!0).cloneNode(!0).lastChild.checked, b.appendChecked = g.checked, h.removeChild(g), h.appendChild(n), n.attachEvent) for (l in {
			submit: 1,
			change: 1,
			focusin: 1
		}) k = "on" + l, m = k in n, m || (n.setAttribute(k, "return;"), m = "function" == typeof n[k]), b[l + "Bubbles"] = m;
		return h.removeChild(n), h = e = f = n = g = null, J(function() {
			var c, d, e, g, h, j, k, l, o, p, q, r, s = G.getElementsByTagName("body")[0];
			!s || (k = 1, r = "padding:0;margin:0;border:", p = "position:absolute;top:0;left:0;width:1px;height:1px;", q = r + "0;visibility:hidden;", l = "style='" + p + r + "5px solid #000;", o = "<div " + l + "display:block;'><div style='" + r + "0;display:block;overflow:hidden;'></div></div>" + "<table " + l + "' cellpadding='0' cellspacing='0'>" + "<tr><td></td></tr></table>", c = G.createElement("div"), c.style.cssText = q + "width:0;height:0;position:static;top:0;margin-top:" + k + "px", s.insertBefore(c, s.firstChild), n = G.createElement("div"), c.appendChild(n), n.innerHTML = "<table><tr><td style='" + r + "0;display:none'></td><td>t</td></tr></table>", i = n.getElementsByTagName("td"), m = 0 === i[0].offsetHeight, i[0].style.display = "", i[1].style.display = "none", b.reliableHiddenOffsets = m && 0 === i[0].offsetHeight, a.getComputedStyle && (n.innerHTML = "", j = G.createElement("div"), j.style.width = "0", j.style.marginRight = "0", n.style.width = "2px", n.appendChild(j), b.reliableMarginRight = 0 === (parseInt((a.getComputedStyle(j, null) || {
				marginRight: 0
			}).marginRight, 10) || 0)), "undefined" != typeof n.style.zoom && (n.innerHTML = "", n.style.width = n.style.padding = "1px", n.style.border = 0, n.style.overflow = "hidden", n.style.display = "inline", n.style.zoom = 1, b.inlineBlockNeedsLayout = 3 === n.offsetWidth, n.style.display = "block", n.style.overflow = "visible", n.innerHTML = "<div style='width:5px;'></div>", b.shrinkWrapBlocks = 3 !== n.offsetWidth), n.style.cssText = p + q, n.innerHTML = o, d = n.firstChild, e = d.firstChild, g = d.nextSibling.firstChild.firstChild, h = {
				doesNotAddBorder: 5 !== e.offsetTop,
				doesAddBorderForTableAndCells: 5 === g.offsetTop
			}, e.style.position = "fixed", e.style.top = "20px", h.fixedPosition = 20 === e.offsetTop || 15 === e.offsetTop, e.style.position = e.style.top = "", d.style.overflow = "hidden", d.style.position = "relative", h.subtractsBorderForOverflowNotVisible = -5 === e.offsetTop, h.doesNotIncludeMarginInBodyOffset = s.offsetTop !== k, a.getComputedStyle && (n.style.marginTop = "1%", b.pixelMargin = "1%" !== (a.getComputedStyle(n, null) || {
				marginTop: 0
			}).marginTop), "undefined" != typeof c.style.zoom && (c.style.zoom = 1), s.removeChild(c), j = n = c = null, J.extend(b, h))
		}), b
	}(), M = /^(?:\{.*\}|\[.*\])$/, N = /([A-Z])/g, J.extend({
		cache: {},
		uuid: 0,
		expando: "jQuery" + (J.fn.jquery + Math.random()).replace(/\D/g, ""),
		noData: {
			embed: !0,
			object: "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",
			applet: !0
		},
		hasData: function(a) {
			return a = a.nodeType ? J.cache[a[J.expando]] : a[J.expando], !! a && !D(a)
		},
		data: function(a, c, d, e) {
			if (J.acceptData(a)) {
				var f, g, h, i = J.expando,
					j = "string" == typeof c,
					k = a.nodeType,
					l = k ? J.cache : a,
					m = k ? a[i] : a[i] && i,
					n = "events" === c;
				if (!(m && l[m] && (n || e || l[m].data) || !j || d !== b)) return;
				return m || (k ? a[i] = m = ++J.uuid : m = i), l[m] || (l[m] = {}, k || (l[m].toJSON = J.noop)), ("object" == typeof c || "function" == typeof c) && (e ? l[m] = J.extend(l[m], c) : l[m].data = J.extend(l[m].data, c)), f = g = l[m], e || (g.data || (g.data = {}), g = g.data), d !== b && (g[J.camelCase(c)] = d), n && !g[c] ? f.events : (j ? (h = g[c], null == h && (h = g[J.camelCase(c)])) : h = g, h)
			}
		},
		removeData: function(a, b, c) {
			if (J.acceptData(a)) {
				var d, e, f, g = J.expando,
					h = a.nodeType,
					i = h ? J.cache : a,
					j = h ? a[g] : g;
				if (!i[j]) return;
				if (b && (d = c ? i[j] : i[j].data)) {
					J.isArray(b) || (b in d ? b = [b] : (b = J.camelCase(b), b = b in d ? [b] : b.split(" ")));
					for (e = 0, f = b.length; f > e; e++) delete d[b[e]];
					if (!(c ? D : J.isEmptyObject)(d)) return
				}
				if (!c && (delete i[j].data, !D(i[j]))) return;
				J.support.deleteExpando || !i.setInterval ? delete i[j] : i[j] = null, h && (J.support.deleteExpando ? delete a[g] : a.removeAttribute ? a.removeAttribute(g) : a[g] = null)
			}
		},
		_data: function(a, b, c) {
			return J.data(a, b, c, !0)
		},
		acceptData: function(a) {
			if (a.nodeName) {
				var b = J.noData[a.nodeName.toLowerCase()];
				if (b) return b !== !0 && a.getAttribute("classid") === b
			}
			return !0
		}
	}), J.fn.extend({
		data: function(a, c) {
			var d, e, f, g, h, i = this[0],
				j = 0,
				k = null;
			if (a === b) {
				if (this.length && (k = J.data(i), 1 === i.nodeType && !J._data(i, "parsedAttrs"))) {
					for (f = i.attributes, h = f.length; h > j; j++) g = f[j].name, 0 === g.indexOf("data-") && (g = J.camelCase(g.substring(5)), E(i, g, k[g]));
					J._data(i, "parsedAttrs", !0)
				}
				return k
			}
			return "object" == typeof a ? this.each(function() {
				J.data(this, a)
			}) : (d = a.split(".", 2), d[1] = d[1] ? "." + d[1] : "", e = d[1] + "!", J.access(this, function(c) {
				return c === b ? (k = this.triggerHandler("getData" + e, [d[0]]), k === b && i && (k = J.data(i, a), k = E(i, a, k)), k === b && d[1] ? this.data(d[0]) : k) : (d[1] = c, this.each(function() {
					var b = J(this);
					b.triggerHandler("setData" + e, d), J.data(this, a, c), b.triggerHandler("changeData" + e, d)
				}), void 0)
			}, null, c, arguments.length > 1, null, !1))
		},
		removeData: function(a) {
			return this.each(function() {
				J.removeData(this, a)
			})
		}
	}), J.extend({
		_mark: function(a, b) {
			a && (b = (b || "fx") + "mark", J._data(a, b, (J._data(a, b) || 0) + 1))
		},
		_unmark: function(a, b, c) {
			if (a !== !0 && (c = b, b = a, a = !1), b) {
				c = c || "fx";
				var d = c + "mark",
					e = a ? 0 : (J._data(b, d) || 1) - 1;
				e ? J._data(b, d, e) : (J.removeData(b, d, !0), C(b, c, "mark"))
			}
		},
		queue: function(a, b, c) {
			var d;
			return a ? (b = (b || "fx") + "queue", d = J._data(a, b), c && (!d || J.isArray(c) ? d = J._data(a, b, J.makeArray(c)) : d.push(c)), d || []) : void 0
		},
		dequeue: function(a, b) {
			b = b || "fx";
			var c = J.queue(a, b),
				d = c.shift(),
				e = {};
			"inprogress" === d && (d = c.shift()), d && ("fx" === b && c.unshift("inprogress"), J._data(a, b + ".run", e), d.call(a, function() {
				J.dequeue(a, b)
			}, e)), c.length || (J.removeData(a, b + "queue " + b + ".run", !0), C(a, b, "queue"))
		}
	}), J.fn.extend({
		queue: function(a, c) {
			var d = 2;
			return "string" != typeof a && (c = a, a = "fx", d--), arguments.length < d ? J.queue(this[0], a) : c === b ? this : this.each(function() {
				var b = J.queue(this, a, c);
				"fx" === a && "inprogress" !== b[0] && J.dequeue(this, a)
			})
		},
		dequeue: function(a) {
			return this.each(function() {
				J.dequeue(this, a)
			})
		},
		delay: function(a, b) {
			return a = J.fx ? J.fx.speeds[a] || a : a, b = b || "fx", this.queue(b, function(b, c) {
				var d = setTimeout(b, a);
				c.stop = function() {
					clearTimeout(d)
				}
			})
		},
		clearQueue: function(a) {
			return this.queue(a || "fx", [])
		},
		promise: function(a, c) {
			function d() {
				--h || e.resolveWith(f, [f])
			}
			"string" != typeof a && (c = a, a = b), a = a || "fx";
			for (var l, e = J.Deferred(), f = this, g = f.length, h = 1, i = a + "defer", j = a + "queue", k = a + "mark"; g--;)(l = J.data(f[g], i, b, !0) || (J.data(f[g], j, b, !0) || J.data(f[g], k, b, !0)) && J.data(f[g], i, J.Callbacks("once memory"), !0)) && (h++, l.add(d));
			return d(), e.promise(c)
		}
	}), O = /[\n\t\r]/g, P = /\s+/, Q = /\r/g, R = /^(?:button|input)$/i, S = /^(?:button|input|object|select|textarea)$/i, T = /^a(?:rea)?$/i, U = /^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i, V = J.support.getSetAttribute, J.fn.extend({
		attr: function(a, b) {
			return J.access(this, J.attr, a, b, arguments.length > 1)
		},
		removeAttr: function(a) {
			return this.each(function() {
				J.removeAttr(this, a)
			})
		},
		prop: function(a, b) {
			return J.access(this, J.prop, a, b, arguments.length > 1)
		},
		removeProp: function(a) {
			return a = J.propFix[a] || a, this.each(function() {
				try {
					this[a] = b, delete this[a]
				} catch (c) {}
			})
		},
		addClass: function(a) {
			var b, c, d, e, f, g, h;
			if (J.isFunction(a)) return this.each(function(b) {
				J(this).addClass(a.call(this, b, this.className))
			});
			if (a && "string" == typeof a) for (b = a.split(P), c = 0, d = this.length; d > c; c++) if (e = this[c], 1 === e.nodeType) if (e.className || 1 !== b.length) {
				for (f = " " + e.className + " ", g = 0, h = b.length; h > g; g++)~f.indexOf(" " + b[g] + " ") || (f += b[g] + " ");
				e.className = J.trim(f)
			} else e.className = a;
			return this
		},
		removeClass: function(a) {
			var c, d, e, f, g, h, i;
			if (J.isFunction(a)) return this.each(function(b) {
				J(this).removeClass(a.call(this, b, this.className))
			});
			if (a && "string" == typeof a || a === b) for (c = (a || "").split(P), d = 0, e = this.length; e > d; d++) if (f = this[d], 1 === f.nodeType && f.className) if (a) {
				for (g = (" " + f.className + " ").replace(O, " "), h = 0, i = c.length; i > h; h++) g = g.replace(" " + c[h] + " ", " ");
				f.className = J.trim(g)
			} else f.className = "";
			return this
		},
		toggleClass: function(a, b) {
			var c = typeof a,
				d = "boolean" == typeof b;
			return J.isFunction(a) ? this.each(function(c) {
				J(this).toggleClass(a.call(this, c, this.className, b), b)
			}) : this.each(function() {
				if ("string" === c) for (var e, f = 0, g = J(this), h = b, i = a.split(P); e = i[f++];) h = d ? h : !g.hasClass(e), g[h ? "addClass" : "removeClass"](e);
				else("undefined" === c || "boolean" === c) && (this.className && J._data(this, "__className__", this.className), this.className = this.className || a === !1 ? "" : J._data(this, "__className__") || "")
			})
		},
		hasClass: function(a) {
			for (var b = " " + a + " ", c = 0, d = this.length; d > c; c++) if (1 === this[c].nodeType && (" " + this[c].className + " ").replace(O, " ").indexOf(b) > -1) return !0;
			return !1
		},
		val: function(a) {
			var c, d, e, f = this[0];
			return arguments.length ? (e = J.isFunction(a), this.each(function(d) {
				var g, f = J(this);
				1 === this.nodeType && (g = e ? a.call(this, d, f.val()) : a, null == g ? g = "" : "number" == typeof g ? g += "" : J.isArray(g) && (g = J.map(g, function(a) {
					return null == a ? "" : a + ""
				})), c = J.valHooks[this.type] || J.valHooks[this.nodeName.toLowerCase()], c && "set" in c && c.set(this, g, "value") !== b || (this.value = g))
			})) : f ? (c = J.valHooks[f.type] || J.valHooks[f.nodeName.toLowerCase()], c && "get" in c && (d = c.get(f, "value")) !== b ? d : (d = f.value, "string" == typeof d ? d.replace(Q, "") : null == d ? "" : d)) : void 0
		}
	}), J.extend({
		valHooks: {
			option: {
				get: function(a) {
					var b = a.attributes.value;
					return !b || b.specified ? a.value : a.text
				}
			},
			select: {
				get: function(a) {
					var b, c, d, e, f = a.selectedIndex,
						g = [],
						h = a.options,
						i = "select-one" === a.type;
					if (0 > f) return null;
					for (c = i ? f : 0, d = i ? f + 1 : h.length; d > c; c++) if (e = h[c], !(!e.selected || (J.support.optDisabled ? e.disabled : null !== e.getAttribute("disabled")) || e.parentNode.disabled && J.nodeName(e.parentNode, "optgroup"))) {
						if (b = J(e).val(), i) return b;
						g.push(b)
					}
					return i && !g.length && h.length ? J(h[f]).val() : g
				},
				set: function(a, b) {
					var c = J.makeArray(b);
					return J(a).find("option").each(function() {
						this.selected = J.inArray(J(this).val(), c) >= 0
					}), c.length || (a.selectedIndex = -1), c
				}
			}
		},
		attrFn: {
			val: !0,
			css: !0,
			html: !0,
			text: !0,
			data: !0,
			width: !0,
			height: !0,
			offset: !0
		},
		attr: function(a, c, d, e) {
			var f, g, h, i = a.nodeType;
			return a && 3 !== i && 8 !== i && 2 !== i ? e && c in J.attrFn ? J(a)[c](d) : "undefined" == typeof a.getAttribute ? J.prop(a, c, d) : (h = 1 !== i || !J.isXMLDoc(a), h && (c = c.toLowerCase(), g = J.attrHooks[c] || (U.test(c) ? X : W)), d !== b ? null === d ? (J.removeAttr(a, c), void 0) : g && "set" in g && h && (f = g.set(a, d, c)) !== b ? f : (a.setAttribute(c, "" + d), d) : g && "get" in g && h && null !== (f = g.get(a, c)) ? f : (f = a.getAttribute(c), null === f ? b : f)) : void 0
		},
		removeAttr: function(a, b) {
			var c, d, e, f, g, h = 0;
			if (b && 1 === a.nodeType) for (d = b.toLowerCase().split(P), f = d.length; f > h; h++) e = d[h], e && (c = J.propFix[e] || e, g = U.test(e), g || J.attr(a, e, ""), a.removeAttribute(V ? e : c), g && c in a && (a[c] = !1))
		},
		attrHooks: {
			type: {
				set: function(a, b) {
					if (R.test(a.nodeName) && a.parentNode) J.error("type property can't be changed");
					else if (!J.support.radioValue && "radio" === b && J.nodeName(a, "input")) {
						var c = a.value;
						return a.setAttribute("type", b), c && (a.value = c), b
					}
				}
			},
			value: {
				get: function(a, b) {
					return W && J.nodeName(a, "button") ? W.get(a, b) : b in a ? a.value : null
				},
				set: function(a, b, c) {
					return W && J.nodeName(a, "button") ? W.set(a, b, c) : (a.value = b, void 0)
				}
			}
		},
		propFix: {
			tabindex: "tabIndex",
			readonly: "readOnly",
			"for": "htmlFor",
			"class": "className",
			maxlength: "maxLength",
			cellspacing: "cellSpacing",
			cellpadding: "cellPadding",
			rowspan: "rowSpan",
			colspan: "colSpan",
			usemap: "useMap",
			frameborder: "frameBorder",
			contenteditable: "contentEditable"
		},
		prop: function(a, c, d) {
			var e, f, g, h = a.nodeType;
			return a && 3 !== h && 8 !== h && 2 !== h ? (g = 1 !== h || !J.isXMLDoc(a), g && (c = J.propFix[c] || c, f = J.propHooks[c]), d !== b ? f && "set" in f && (e = f.set(a, d, c)) !== b ? e : a[c] = d : f && "get" in f && null !== (e = f.get(a, c)) ? e : a[c]) : void 0
		},
		propHooks: {
			tabIndex: {
				get: function(a) {
					var c = a.getAttributeNode("tabindex");
					return c && c.specified ? parseInt(c.value, 10) : S.test(a.nodeName) || T.test(a.nodeName) && a.href ? 0 : b
				}
			}
		}
	}), J.attrHooks.tabindex = J.propHooks.tabIndex, X = {
		get: function(a, c) {
			var d, e = J.prop(a, c);
			return e === !0 || "boolean" != typeof e && (d = a.getAttributeNode(c)) && d.nodeValue !== !1 ? c.toLowerCase() : b
		},
		set: function(a, b, c) {
			var d;
			return b === !1 ? J.removeAttr(a, c) : (d = J.propFix[c] || c, d in a && (a[d] = !0), a.setAttribute(c, c.toLowerCase())), c
		}
	}, V || (Y = {
		name: !0,
		id: !0,
		coords: !0
	}, W = J.valHooks.button = {
		get: function(a, c) {
			var d;
			return d = a.getAttributeNode(c), d && (Y[c] ? "" !== d.nodeValue : d.specified) ? d.nodeValue : b
		},
		set: function(a, b, c) {
			var d = a.getAttributeNode(c);
			return d || (d = G.createAttribute(c), a.setAttributeNode(d)), d.nodeValue = b + ""
		}
	}, J.attrHooks.tabindex.set = W.set, J.each(["width", "height"], function(a, b) {
		J.attrHooks[b] = J.extend(J.attrHooks[b], {
			set: function(a, c) {
				return "" === c ? (a.setAttribute(b, "auto"), c) : void 0
			}
		})
	}), J.attrHooks.contenteditable = {
		get: W.get,
		set: function(a, b, c) {
			"" === b && (b = "false"), W.set(a, b, c)
		}
	}), J.support.hrefNormalized || J.each(["href", "src", "width", "height"], function(a, c) {
		J.attrHooks[c] = J.extend(J.attrHooks[c], {
			get: function(a) {
				var d = a.getAttribute(c, 2);
				return null === d ? b : d
			}
		})
	}), J.support.style || (J.attrHooks.style = {
		get: function(a) {
			return a.style.cssText.toLowerCase() || b
		},
		set: function(a, b) {
			return a.style.cssText = "" + b
		}
	}), J.support.optSelected || (J.propHooks.selected = J.extend(J.propHooks.selected, {
		get: function(a) {
			var b = a.parentNode;
			return b && (b.selectedIndex, b.parentNode && b.parentNode.selectedIndex), null
		}
	})), J.support.enctype || (J.propFix.enctype = "encoding"), J.support.checkOn || J.each(["radio", "checkbox"], function() {
		J.valHooks[this] = {
			get: function(a) {
				return null === a.getAttribute("value") ? "on" : a.value
			}
		}
	}), J.each(["radio", "checkbox"], function() {
		J.valHooks[this] = J.extend(J.valHooks[this], {
			set: function(a, b) {
				return J.isArray(b) ? a.checked = J.inArray(J(a).val(), b) >= 0 : void 0
			}
		})
	}), Z = /^(?:textarea|input|select)$/i, $ = /^([^\.]*)?(?:\.(.+))?$/, _ = /(?:^|\s)hover(\.\S+)?\b/, ab = /^key/, bb = /^(?:mouse|contextmenu)|click/, cb = /^(?:focusinfocus|focusoutblur)$/, db = /^(\w*)(?:#([\w\-]+))?(?:\.([\w\-]+))?$/, eb = function(a) {
		var b = db.exec(a);
		return b && (b[1] = (b[1] || "").toLowerCase(), b[3] = b[3] && new RegExp("(?:^|\\s)" + b[3] + "(?:\\s|$)")), b
	}, fb = function(a, b) {
		var c = a.attributes || {};
		return !(b[1] && a.nodeName.toLowerCase() !== b[1] || b[2] && (c.id || {}).value !== b[2] || b[3] && !b[3].test((c["class"] || {}).value))
	}, gb = function(a) {
		return J.event.special.hover ? a : a.replace(_, "mouseenter$1 mouseleave$1")
	}, J.event = {
		add: function(a, c, d, e, f) {
			var g, h, i, j, k, l, m, n, o, q, r;
			if (3 !== a.nodeType && 8 !== a.nodeType && c && d && (g = J._data(a))) {
				for (d.handler && (o = d, d = o.handler, f = o.selector), d.guid || (d.guid = J.guid++), i = g.events, i || (g.events = i = {}), h = g.handle, h || (g.handle = h = function(a) {
					return "undefined" == typeof J || a && J.event.triggered === a.type ? b : J.event.dispatch.apply(h.elem, arguments)
				}, h.elem = a), c = J.trim(gb(c)).split(" "), j = 0; j < c.length; j++) k = $.exec(c[j]) || [], l = k[1], m = (k[2] || "").split(".").sort(), r = J.event.special[l] || {}, l = (f ? r.delegateType : r.bindType) || l, r = J.event.special[l] || {}, n = J.extend({
					type: l,
					origType: k[1],
					data: e,
					handler: d,
					guid: d.guid,
					selector: f,
					quick: f && eb(f),
					namespace: m.join(".")
				}, o), q = i[l], q || (q = i[l] = [], q.delegateCount = 0, r.setup && r.setup.call(a, e, m, h) !== !1 || (a.addEventListener ? a.addEventListener(l, h, !1) : a.attachEvent && a.attachEvent("on" + l, h))), r.add && (r.add.call(a, n), n.handler.guid || (n.handler.guid = d.guid)), f ? q.splice(q.delegateCount++, 0, n) : q.push(n), J.event.global[l] = !0;
				a = null
			}
		},
		global: {},
		remove: function(a, b, c, d, e) {
			var g, h, i, j, k, l, m, n, o, p, q, r, f = J.hasData(a) && J._data(a);
			if (f && (n = f.events)) {
				for (b = J.trim(gb(b || "")).split(" "), g = 0; g < b.length; g++) if (h = $.exec(b[g]) || [], i = j = h[1], k = h[2], i) {
					for (o = J.event.special[i] || {}, i = (d ? o.delegateType : o.bindType) || i, q = n[i] || [], l = q.length, k = k ? new RegExp("(^|\\.)" + k.split(".").sort().join("\\.(?:.*\\.)?") + "(\\.|$)") : null, m = 0; m < q.length; m++) r = q[m], !(!e && j !== r.origType || c && c.guid !== r.guid || k && !k.test(r.namespace) || d && d !== r.selector && ("**" !== d || !r.selector) || (q.splice(m--, 1), r.selector && q.delegateCount--, !o.remove || !o.remove.call(a, r)));
					0 === q.length && l !== q.length && ((!o.teardown || o.teardown.call(a, k) === !1) && J.removeEvent(a, i, f.handle), delete n[i])
				} else for (i in n) J.event.remove(a, i + b[g], c, d, !0);
				J.isEmptyObject(n) && (p = f.handle, p && (p.elem = null), J.removeData(a, ["events", "handle"], !0))
			}
		},
		customEvent: {
			getData: !0,
			setData: !0,
			changeData: !0
		},
		trigger: function(c, d, e, f) {
			if (!e || 3 !== e.nodeType && 8 !== e.nodeType) {
				var i, j, k, l, m, n, o, p, q, r, g = c.type || c,
					h = [];
				if (cb.test(g + J.event.triggered)) return;
				if (g.indexOf("!") >= 0 && (g = g.slice(0, -1), j = !0), g.indexOf(".") >= 0 && (h = g.split("."), g = h.shift(), h.sort()), (!e || J.event.customEvent[g]) && !J.event.global[g]) return;
				if (c = "object" == typeof c ? c[J.expando] ? c : new J.Event(g, c) : new J.Event(g), c.type = g, c.isTrigger = !0, c.exclusive = j, c.namespace = h.join("."), c.namespace_re = c.namespace ? new RegExp("(^|\\.)" + h.join("\\.(?:.*\\.)?") + "(\\.|$)") : null, n = g.indexOf(":") < 0 ? "on" + g : "", !e) {
					i = J.cache;
					for (k in i) i[k].events && i[k].events[g] && J.event.trigger(c, d, i[k].handle.elem, !0);
					return
				}
				if (c.result = b, c.target || (c.target = e), d = null != d ? J.makeArray(d) : [], d.unshift(c), o = J.event.special[g] || {}, o.trigger && o.trigger.apply(e, d) === !1) return;
				if (q = [
					[e, o.bindType || g]
				], !f && !o.noBubble && !J.isWindow(e)) {
					for (r = o.delegateType || g, l = cb.test(r + g) ? e : e.parentNode, m = null; l; l = l.parentNode) q.push([l, r]), m = l;
					m && m === e.ownerDocument && q.push([m.defaultView || m.parentWindow || a, r])
				}
				for (k = 0; k < q.length && !c.isPropagationStopped(); k++) l = q[k][0], c.type = q[k][1], p = (J._data(l, "events") || {})[c.type] && J._data(l, "handle"), p && p.apply(l, d), p = n && l[n], p && J.acceptData(l) && p.apply(l, d) === !1 && c.preventDefault();
				return c.type = g, !(f || c.isDefaultPrevented() || o._default && o._default.apply(e.ownerDocument, d) !== !1 || "click" === g && J.nodeName(e, "a") || !J.acceptData(e) || !n || !e[g] || ("focus" === g || "blur" === g) && 0 === c.target.offsetWidth || J.isWindow(e) || (m = e[n], m && (e[n] = null), J.event.triggered = g, e[g](), J.event.triggered = b, !m || !(e[n] = m))), c.result
			}
		},
		dispatch: function(c) {
			c = J.event.fix(c || a.event);
			var j, k, l, m, n, o, p, q, r, s, d = (J._data(this, "events") || {})[c.type] || [],
				e = d.delegateCount,
				f = [].slice.call(arguments, 0),
				g = !c.exclusive && !c.namespace,
				h = J.event.special[c.type] || {},
				i = [];
			if (f[0] = c, c.delegateTarget = this, !h.preDispatch || h.preDispatch.call(this, c) !== !1) {
				if (e && (!c.button || "click" !== c.type)) for (m = J(this), m.context = this.ownerDocument || this, l = c.target; l != this; l = l.parentNode || this) if (l.disabled !== !0) {
					for (o = {}, q = [], m[0] = l, j = 0; e > j; j++) r = d[j], s = r.selector, o[s] === b && (o[s] = r.quick ? fb(l, r.quick) : m.is(s)), o[s] && q.push(r);
					q.length && i.push({
						elem: l,
						matches: q
					})
				}
				for (d.length > e && i.push({
					elem: this,
					matches: d.slice(e)
				}), j = 0; j < i.length && !c.isPropagationStopped(); j++) for (p = i[j], c.currentTarget = p.elem, k = 0; k < p.matches.length && !c.isImmediatePropagationStopped(); k++) r = p.matches[k], (g || !c.namespace && !r.namespace || c.namespace_re && c.namespace_re.test(r.namespace)) && (c.data = r.data, c.handleObj = r, n = ((J.event.special[r.origType] || {}).handle || r.handler).apply(p.elem, f), n !== b && (c.result = n, n === !1 && (c.preventDefault(), c.stopPropagation())));
				return h.postDispatch && h.postDispatch.call(this, c), c.result
			}
		},
		props: "attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),
		fixHooks: {},
		keyHooks: {
			props: "char charCode key keyCode".split(" "),
			filter: function(a, b) {
				return null == a.which && (a.which = null != b.charCode ? b.charCode : b.keyCode), a
			}
		},
		mouseHooks: {
			props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
			filter: function(a, c) {
				var d, e, f, g = c.button,
					h = c.fromElement;
				return null == a.pageX && null != c.clientX && (d = a.target.ownerDocument || G, e = d.documentElement, f = d.body, a.pageX = c.clientX + (e && e.scrollLeft || f && f.scrollLeft || 0) - (e && e.clientLeft || f && f.clientLeft || 0), a.pageY = c.clientY + (e && e.scrollTop || f && f.scrollTop || 0) - (e && e.clientTop || f && f.clientTop || 0)), !a.relatedTarget && h && (a.relatedTarget = h === a.target ? c.toElement : h), !a.which && g !== b && (a.which = 1 & g ? 1 : 2 & g ? 3 : 4 & g ? 2 : 0), a
			}
		},
		fix: function(a) {
			if (a[J.expando]) return a;
			var c, d, e = a,
				f = J.event.fixHooks[a.type] || {},
				g = f.props ? this.props.concat(f.props) : this.props;
			for (a = J.Event(e), c = g.length; c;) d = g[--c], a[d] = e[d];
			return a.target || (a.target = e.srcElement || G), 3 === a.target.nodeType && (a.target = a.target.parentNode), a.metaKey === b && (a.metaKey = a.ctrlKey), f.filter ? f.filter(a, e) : a
		},
		special: {
			ready: {
				setup: J.bindReady
			},
			load: {
				noBubble: !0
			},
			focus: {
				delegateType: "focusin"
			},
			blur: {
				delegateType: "focusout"
			},
			beforeunload: {
				setup: function(a, b, c) {
					J.isWindow(this) && (this.onbeforeunload = c)
				},
				teardown: function(a, b) {
					this.onbeforeunload === b && (this.onbeforeunload = null)
				}
			}
		},
		simulate: function(a, b, c, d) {
			var e = J.extend(new J.Event, c, {
				type: a,
				isSimulated: !0,
				originalEvent: {}
			});
			d ? J.event.trigger(e, null, b) : J.event.dispatch.call(b, e), e.isDefaultPrevented() && c.preventDefault()
		}
	}, J.event.handle = J.event.dispatch, J.removeEvent = G.removeEventListener ?
	function(a, b, c) {
		a.removeEventListener && a.removeEventListener(b, c, !1)
	} : function(a, b, c) {
		a.detachEvent && a.detachEvent("on" + b, c)
	}, J.Event = function(a, b) {
		return this instanceof J.Event ? (a && a.type ? (this.originalEvent = a, this.type = a.type, this.isDefaultPrevented = a.defaultPrevented || a.returnValue === !1 || a.getPreventDefault && a.getPreventDefault() ? A : B) : this.type = a, b && J.extend(this, b), this.timeStamp = a && a.timeStamp || J.now(), this[J.expando] = !0, void 0) : new J.Event(a, b)
	}, J.Event.prototype = {
		preventDefault: function() {
			this.isDefaultPrevented = A;
			var a = this.originalEvent;
			!a || (a.preventDefault ? a.preventDefault() : a.returnValue = !1)
		},
		stopPropagation: function() {
			this.isPropagationStopped = A;
			var a = this.originalEvent;
			!a || (a.stopPropagation && a.stopPropagation(), a.cancelBubble = !0)
		},

		stopImmediatePropagation: function() {
			this.isImmediatePropagationStopped = A, this.stopPropagation()
		},
		isDefaultPrevented: B,
		isPropagationStopped: B,
		isImmediatePropagationStopped: B
	}, J.each({
		mouseenter: "mouseover",
		mouseleave: "mouseout"
	}, function(a, b) {
		J.event.special[a] = {
			delegateType: b,
			bindType: b,
			handle: function(a) {
				var g, c = this,
					d = a.relatedTarget,
					e = a.handleObj;
				return e.selector, (!d || d !== c && !J.contains(c, d)) && (a.type = e.origType, g = e.handler.apply(this, arguments), a.type = b), g
			}
		}
	}), J.support.submitBubbles || (J.event.special.submit = {
		setup: function() {
			return J.nodeName(this, "form") ? !1 : (J.event.add(this, "click._submit keypress._submit", function(a) {
				var c = a.target,
					d = J.nodeName(c, "input") || J.nodeName(c, "button") ? c.form : b;
				d && !d._submit_attached && (J.event.add(d, "submit._submit", function(a) {
					a._submit_bubble = !0
				}), d._submit_attached = !0)
			}), void 0)
		},
		postDispatch: function(a) {
			a._submit_bubble && (delete a._submit_bubble, this.parentNode && !a.isTrigger && J.event.simulate("submit", this.parentNode, a, !0))
		},
		teardown: function() {
			return J.nodeName(this, "form") ? !1 : (J.event.remove(this, "._submit"), void 0)
		}
	}), J.support.changeBubbles || (J.event.special.change = {
		setup: function() {
			return Z.test(this.nodeName) ? (("checkbox" === this.type || "radio" === this.type) && (J.event.add(this, "propertychange._change", function(a) {
				"checked" === a.originalEvent.propertyName && (this._just_changed = !0)
			}), J.event.add(this, "click._change", function(a) {
				this._just_changed && !a.isTrigger && (this._just_changed = !1, J.event.simulate("change", this, a, !0))
			})), !1) : (J.event.add(this, "beforeactivate._change", function(a) {
				var b = a.target;
				Z.test(b.nodeName) && !b._change_attached && (J.event.add(b, "change._change", function(a) {
					this.parentNode && !a.isSimulated && !a.isTrigger && J.event.simulate("change", this.parentNode, a, !0)
				}), b._change_attached = !0)
			}), void 0)
		},
		handle: function(a) {
			var b = a.target;
			return this !== b || a.isSimulated || a.isTrigger || "radio" !== b.type && "checkbox" !== b.type ? a.handleObj.handler.apply(this, arguments) : void 0
		},
		teardown: function() {
			return J.event.remove(this, "._change"), Z.test(this.nodeName)
		}
	}), J.support.focusinBubbles || J.each({
		focus: "focusin",
		blur: "focusout"
	}, function(a, b) {
		var c = 0,
			d = function(a) {
				J.event.simulate(b, a.target, J.event.fix(a), !0)
			};
		J.event.special[b] = {
			setup: function() {
				0 === c++ && G.addEventListener(a, d, !0)
			},
			teardown: function() {
				0 === --c && G.removeEventListener(a, d, !0)
			}
		}
	}), J.fn.extend({
		on: function(a, c, d, e, f) {
			var g, h;
			if ("object" == typeof a) {
				"string" != typeof c && (d = d || c, c = b);
				for (h in a) this.on(h, c, d, a[h], f);
				return this
			}
			if (null == d && null == e ? (e = c, d = c = b) : null == e && ("string" == typeof c ? (e = d, d = b) : (e = d, d = c, c = b)), e === !1) e = B;
			else if (!e) return this;
			return 1 === f && (g = e, e = function(a) {
				return J().off(a), g.apply(this, arguments)
			}, e.guid = g.guid || (g.guid = J.guid++)), this.each(function() {
				J.event.add(this, a, e, d, c)
			})
		},
		one: function(a, b, c, d) {
			return this.on(a, b, c, d, 1)
		},
		off: function(a, c, d) {
			var e, f;
			if (a && a.preventDefault && a.handleObj) return e = a.handleObj, J(a.delegateTarget).off(e.namespace ? e.origType + "." + e.namespace : e.origType, e.selector, e.handler), this;
			if ("object" == typeof a) {
				for (f in a) this.off(f, c, a[f]);
				return this
			}
			return (c === !1 || "function" == typeof c) && (d = c, c = b), d === !1 && (d = B), this.each(function() {
				J.event.remove(this, a, d, c)
			})
		},
		bind: function(a, b, c) {
			return this.on(a, null, b, c)
		},
		unbind: function(a, b) {
			return this.off(a, null, b)
		},
		live: function(a, b, c) {
			return J(this.context).on(a, this.selector, b, c), this
		},
		die: function(a, b) {
			return J(this.context).off(a, this.selector || "**", b), this
		},
		delegate: function(a, b, c, d) {
			return this.on(b, a, c, d)
		},
		undelegate: function(a, b, c) {
			return 1 == arguments.length ? this.off(a, "**") : this.off(b, a, c)
		},
		trigger: function(a, b) {
			return this.each(function() {
				J.event.trigger(a, b, this)
			})
		},
		triggerHandler: function(a, b) {
			return this[0] ? J.event.trigger(a, b, this[0], !0) : void 0
		},
		toggle: function(a) {
			var b = arguments,
				c = a.guid || J.guid++,
				d = 0,
				e = function(c) {
					var e = (J._data(this, "lastToggle" + a.guid) || 0) % d;
					return J._data(this, "lastToggle" + a.guid, e + 1), c.preventDefault(), b[e].apply(this, arguments) || !1
				};
			for (e.guid = c; d < b.length;) b[d++].guid = c;
			return this.click(e)
		},
		hover: function(a, b) {
			return this.mouseenter(a).mouseleave(b || a)
		}
	}), J.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "), function(a, b) {
		J.fn[b] = function(a, c) {
			return null == c && (c = a, a = null), arguments.length > 0 ? this.on(b, null, a, c) : this.trigger(b)
		}, J.attrFn && (J.attrFn[b] = !0), ab.test(b) && (J.event.fixHooks[b] = J.event.keyHooks), bb.test(b) && (J.event.fixHooks[b] = J.event.mouseHooks)
	}), function() {
		function a(a, b, c, d, f, g) {
			var h, i, j, k;
			for (h = 0, i = d.length; i > h; h++) if (j = d[h]) {
				for (k = !1, j = j[a]; j;) {
					if (j[e] === c) {
						k = d[j.sizset];
						break
					}
					if (1 === j.nodeType) if (g || (j[e] = c, j.sizset = h), "string" != typeof b) {
						if (j === b) {
							k = !0;
							break
						}
					} else if (m.filter(b, [j]).length > 0) {
						k = j;
						break
					}
					j = j[a]
				}
				d[h] = k
			}
		}
		function c(a, b, c, d, f, g) {
			var h, i, j, k;
			for (h = 0, i = d.length; i > h; h++) if (j = d[h]) {
				for (k = !1, j = j[a]; j;) {
					if (j[e] === c) {
						k = d[j.sizset];
						break
					}
					if (1 === j.nodeType && !g && (j[e] = c, j.sizset = h), j.nodeName.toLowerCase() === b) {
						k = j;
						break
					}
					j = j[a]
				}
				d[h] = k
			}
		}
		var m, n, o, p, q, r, s, u, v, w, d = /((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,
			e = "sizcache" + (Math.random() + "").replace(".", ""),
			f = 0,
			g = Object.prototype.toString,
			h = !1,
			i = !0,
			j = /\\/g,
			k = /\r\n/g,
			l = /\W/;
		[0, 0].sort(function() {
			return i = !1, 0
		}), m = function(a, b, c, e) {
			var f, h, i, j, k, l, n, q, r, t, u, v, x;
			if (c = c || [], b = b || G, f = b, 1 !== b.nodeType && 9 !== b.nodeType) return [];
			if (!a || "string" != typeof a) return c;
			t = !0, u = m.isXML(b), v = [], x = a;
			do
			if (d.exec(""), h = d.exec(x), h && (x = h[3], v.push(h[1]), h[2])) {
				k = h[3];
				break
			}
			while (h);
			if (v.length > 1 && p.exec(a)) if (2 === v.length && o.relative[v[0]]) i = w(v[0] + v[1], b, e);
			else for (i = o.relative[v[0]] ? [b] : m(v.shift(), b); v.length;) a = v.shift(), o.relative[a] && (a += v.shift()), i = w(a, i, e);
			else if (!e && v.length > 1 && 9 === b.nodeType && !u && o.match.ID.test(v[0]) && !o.match.ID.test(v[v.length - 1]) && (l = m.find(v.shift(), b, u), b = l.expr ? m.filter(l.expr, l.set)[0] : l.set[0]), b) for (l = e ? {
				expr: v.pop(),
				set: s(e)
			} : m.find(v.pop(), 1 !== v.length || "~" !== v[0] && "+" !== v[0] || !b.parentNode ? b : b.parentNode, u), i = l.expr ? m.filter(l.expr, l.set) : l.set, v.length > 0 ? j = s(i) : t = !1; v.length;) n = v.pop(), q = n, o.relative[n] ? q = v.pop() : n = "", null == q && (q = b), o.relative[n](j, q, u);
			else j = v = [];
			if (j || (j = i), j || m.error(n || a), "[object Array]" === g.call(j)) if (t) if (b && 1 === b.nodeType) for (r = 0; null != j[r]; r++) j[r] && (j[r] === !0 || 1 === j[r].nodeType && m.contains(b, j[r])) && c.push(i[r]);
			else for (r = 0; null != j[r]; r++) j[r] && 1 === j[r].nodeType && c.push(i[r]);
			else c.push.apply(c, j);
			else s(j, c);
			return k && (m(k, f, c, e), m.uniqueSort(c)), c
		}, m.uniqueSort = function(a) {
			if (u && (h = i, a.sort(u), h)) for (var b = 1; b < a.length; b++) a[b] === a[b - 1] && a.splice(b--, 1);
			return a
		}, m.matches = function(a, b) {
			return m(a, null, null, b)
		}, m.matchesSelector = function(a, b) {
			return m(b, null, null, [a]).length > 0
		}, m.find = function(a, b, c) {
			var d, e, f, g, h, i;
			if (!a) return [];
			for (e = 0, f = o.order.length; f > e; e++) if (h = o.order[e], (g = o.leftMatch[h].exec(a)) && (i = g[1], g.splice(1, 1), "\\" !== i.substr(i.length - 1) && (g[1] = (g[1] || "").replace(j, ""), d = o.find[h](g, b, c), null != d))) {
				a = a.replace(o.match[h], "");
				break
			}
			return d || (d = "undefined" != typeof b.getElementsByTagName ? b.getElementsByTagName("*") : []), {
				set: d,
				expr: a
			}
		}, m.filter = function(a, c, d, e) {
			for (var f, g, h, i, j, k, l, n, p, q = a, r = [], s = c, t = c && c[0] && m.isXML(c[0]); a && c.length;) {
				for (h in o.filter) if (null != (f = o.leftMatch[h].exec(a)) && f[2]) {
					if (k = o.filter[h], l = f[1], g = !1, f.splice(1, 1), "\\" === l.substr(l.length - 1)) continue;
					if (s === r && (r = []), o.preFilter[h]) if (f = o.preFilter[h](f, s, d, r, e, t)) {
						if (f === !0) continue
					} else g = i = !0;
					if (f) for (n = 0; null != (j = s[n]); n++) j && (i = k(j, f, n, s), p = e ^ i, d && null != i ? p ? g = !0 : s[n] = !1 : p && (r.push(j), g = !0));
					if (i !== b) {
						if (d || (s = r), a = a.replace(o.match[h], ""), !g) return [];
						break
					}
				}
				if (a === q) {
					if (null != g) break;
					m.error(a)
				}
				q = a
			}
			return s
		}, m.error = function(a) {
			throw new Error("Syntax error, unrecognized expression: " + a)
		}, n = m.getText = function(a) {
			var b, c, d = a.nodeType,
				e = "";
			if (d) {
				if (1 === d || 9 === d || 11 === d) {
					if ("string" == typeof a.textContent) return a.textContent;
					if ("string" == typeof a.innerText) return a.innerText.replace(k, "");
					for (a = a.firstChild; a; a = a.nextSibling) e += n(a)
				} else if (3 === d || 4 === d) return a.nodeValue
			} else for (b = 0; c = a[b]; b++) 8 !== c.nodeType && (e += n(c));
			return e
		}, o = m.selectors = {
			order: ["ID", "NAME", "TAG"],
			match: {
				ID: /#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,
				CLASS: /\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,
				NAME: /\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,
				ATTR: /\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(?:(['"])(.*?)\3|(#?(?:[\w\u00c0-\uFFFF\-]|\\.)*)|)|)\s*\]/,
				TAG: /^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,
				CHILD: /:(only|nth|last|first)-child(?:\(\s*(even|odd|(?:[+\-]?\d+|(?:[+\-]?\d*)?n\s*(?:[+\-]\s*\d+)?))\s*\))?/,
				POS: /:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,
				PSEUDO: /:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/
			},
			leftMatch: {},
			attrMap: {
				"class": "className",
				"for": "htmlFor"
			},
			attrHandle: {
				href: function(a) {
					return a.getAttribute("href")
				},
				type: function(a) {
					return a.getAttribute("type")
				}
			},
			relative: {
				"+": function(a, b) {
					var h, f, g, c = "string" == typeof b,
						d = c && !l.test(b),
						e = c && !d;
					for (d && (b = b.toLowerCase()), f = 0, g = a.length; g > f; f++) if (h = a[f]) {
						for (;
						(h = h.previousSibling) && 1 !== h.nodeType;);
						a[f] = e || h && h.nodeName.toLowerCase() === b ? h || !1 : h === b
					}
					e && m.filter(b, a, !0)
				},
				">": function(a, b) {
					var c, g, d = "string" == typeof b,
						e = 0,
						f = a.length;
					if (d && !l.test(b)) for (b = b.toLowerCase(); f > e; e++) c = a[e], c && (g = c.parentNode, a[e] = g.nodeName.toLowerCase() === b ? g : !1);
					else {
						for (; f > e; e++) c = a[e], c && (a[e] = d ? c.parentNode : c.parentNode === b);
						d && m.filter(b, a, !0)
					}
				},
				"": function(b, d, e) {
					var g, h = f++,
						i = a;
					"string" == typeof d && !l.test(d) && (d = d.toLowerCase(), g = d, i = c), i("parentNode", d, h, b, g, e)
				},
				"~": function(b, d, e) {
					var g, h = f++,
						i = a;
					"string" == typeof d && !l.test(d) && (d = d.toLowerCase(), g = d, i = c), i("previousSibling", d, h, b, g, e)
				}
			},
			find: {
				ID: function(a, b, c) {
					if ("undefined" != typeof b.getElementById && !c) {
						var d = b.getElementById(a[1]);
						return d && d.parentNode ? [d] : []
					}
				},
				NAME: function(a, b) {
					var c, d, e, f;
					if ("undefined" != typeof b.getElementsByName) {
						for (c = [], d = b.getElementsByName(a[1]), e = 0, f = d.length; f > e; e++) d[e].getAttribute("name") === a[1] && c.push(d[e]);
						return 0 === c.length ? null : c
					}
				},
				TAG: function(a, b) {
					return "undefined" != typeof b.getElementsByTagName ? b.getElementsByTagName(a[1]) : void 0
				}
			},
			preFilter: {
				CLASS: function(a, b, c, d, e, f) {
					if (a = " " + a[1].replace(j, "") + " ", f) return a;
					for (var h, g = 0; null != (h = b[g]); g++) h && (e ^ (h.className && (" " + h.className + " ").replace(/[\t\n\r]/g, " ").indexOf(a) >= 0) ? c || d.push(h) : c && (b[g] = !1));
					return !1
				},
				ID: function(a) {
					return a[1].replace(j, "")
				},
				TAG: function(a) {
					return a[1].replace(j, "").toLowerCase()
				},
				CHILD: function(a) {
					if ("nth" === a[1]) {
						a[2] || m.error(a[0]), a[2] = a[2].replace(/^\+|\s*/g, "");
						var b = /(-?)(\d*)(?:n([+\-]?\d*))?/.exec("even" === a[2] && "2n" || "odd" === a[2] && "2n+1" || !/\D/.test(a[2]) && "0n+" + a[2] || a[2]);
						a[2] = b[1] + (b[2] || 1) - 0, a[3] = b[3] - 0
					} else a[2] && m.error(a[0]);
					return a[0] = f++, a
				},
				ATTR: function(a, b, c, d, e, f) {
					var g = a[1] = a[1].replace(j, "");
					return !f && o.attrMap[g] && (a[1] = o.attrMap[g]), a[4] = (a[4] || a[5] || "").replace(j, ""), "~=" === a[2] && (a[4] = " " + a[4] + " "), a
				},
				PSEUDO: function(a, b, c, e, f) {
					if ("not" === a[1]) {
						if (!((d.exec(a[3]) || "").length > 1 || /^\w/.test(a[3]))) {
							var g = m.filter(a[3], b, c, !0 ^ f);
							return c || e.push.apply(e, g), !1
						}
						a[3] = m(a[3], null, null, b)
					} else if (o.match.POS.test(a[0]) || o.match.CHILD.test(a[0])) return !0;
					return a
				},
				POS: function(a) {
					return a.unshift(!0), a
				}
			},
			filters: {
				enabled: function(a) {
					return a.disabled === !1 && "hidden" !== a.type
				},
				disabled: function(a) {
					return a.disabled === !0
				},
				checked: function(a) {
					return a.checked === !0
				},
				selected: function(a) {
					return a.parentNode && a.parentNode.selectedIndex, a.selected === !0
				},
				parent: function(a) {
					return !!a.firstChild
				},
				empty: function(a) {
					return !a.firstChild
				},
				has: function(a, b, c) {
					return !!m(c[3], a).length
				},
				header: function(a) {
					return /h\d/i.test(a.nodeName)
				},
				text: function(a) {
					var b = a.getAttribute("type"),
						c = a.type;
					return "input" === a.nodeName.toLowerCase() && "text" === c && (b === c || null === b)
				},
				radio: function(a) {
					return "input" === a.nodeName.toLowerCase() && "radio" === a.type
				},
				checkbox: function(a) {
					return "input" === a.nodeName.toLowerCase() && "checkbox" === a.type
				},
				file: function(a) {
					return "input" === a.nodeName.toLowerCase() && "file" === a.type
				},
				password: function(a) {
					return "input" === a.nodeName.toLowerCase() && "password" === a.type
				},
				submit: function(a) {
					var b = a.nodeName.toLowerCase();
					return ("input" === b || "button" === b) && "submit" === a.type
				},
				image: function(a) {
					return "input" === a.nodeName.toLowerCase() && "image" === a.type
				},
				reset: function(a) {
					var b = a.nodeName.toLowerCase();
					return ("input" === b || "button" === b) && "reset" === a.type
				},
				button: function(a) {
					var b = a.nodeName.toLowerCase();
					return "input" === b && "button" === a.type || "button" === b
				},
				input: function(a) {
					return /input|select|textarea|button/i.test(a.nodeName)
				},
				focus: function(a) {
					return a === a.ownerDocument.activeElement
				}
			},
			setFilters: {
				first: function(a, b) {
					return 0 === b
				},
				last: function(a, b, c, d) {
					return b === d.length - 1
				},
				even: function(a, b) {
					return 0 === b % 2
				},
				odd: function(a, b) {
					return 1 === b % 2
				},
				lt: function(a, b, c) {
					return b < c[3] - 0
				},
				gt: function(a, b, c) {
					return b > c[3] - 0
				},
				nth: function(a, b, c) {
					return c[3] - 0 === b
				},
				eq: function(a, b, c) {
					return c[3] - 0 === b
				}
			},
			filter: {
				PSEUDO: function(a, b, c, d) {
					var g, h, i, e = b[1],
						f = o.filters[e];
					if (f) return f(a, c, b, d);
					if ("contains" === e) return (a.textContent || a.innerText || n([a]) || "").indexOf(b[3]) >= 0;
					if ("not" === e) {
						for (g = b[3], h = 0, i = g.length; i > h; h++) if (g[h] === a) return !1;
						return !0
					}
					m.error(e)
				},
				CHILD: function(a, b) {
					var c, d, f, g, i, j, k = b[1],
						l = a;
					switch (k) {
					case "only":
					case "first":
						for (; l = l.previousSibling;) if (1 === l.nodeType) return !1;
						if ("first" === k) return !0;
						l = a;
					case "last":
						for (; l = l.nextSibling;) if (1 === l.nodeType) return !1;
						return !0;
					case "nth":
						if (c = b[2], d = b[3], 1 === c && 0 === d) return !0;
						if (f = b[0], g = a.parentNode, g && (g[e] !== f || !a.nodeIndex)) {
							for (i = 0, l = g.firstChild; l; l = l.nextSibling) 1 === l.nodeType && (l.nodeIndex = ++i);
							g[e] = f
						}
						return j = a.nodeIndex - d, 0 === c ? 0 === j : 0 === j % c && j / c >= 0
					}
				},
				ID: function(a, b) {
					return 1 === a.nodeType && a.getAttribute("id") === b
				},
				TAG: function(a, b) {
					return "*" === b && 1 === a.nodeType || !! a.nodeName && a.nodeName.toLowerCase() === b
				},
				CLASS: function(a, b) {
					return (" " + (a.className || a.getAttribute("class")) + " ").indexOf(b) > -1
				},
				ATTR: function(a, b) {
					var c = b[1],
						d = m.attr ? m.attr(a, c) : o.attrHandle[c] ? o.attrHandle[c](a) : null != a[c] ? a[c] : a.getAttribute(c),
						e = d + "",
						f = b[2],
						g = b[4];
					return null == d ? "!=" === f : !f && m.attr ? null != d : "=" === f ? e === g : "*=" === f ? e.indexOf(g) >= 0 : "~=" === f ? (" " + e + " ").indexOf(g) >= 0 : g ? "!=" === f ? e !== g : "^=" === f ? 0 === e.indexOf(g) : "$=" === f ? e.substr(e.length - g.length) === g : "|=" === f ? e === g || e.substr(0, g.length + 1) === g + "-" : !1 : e && d !== !1
				},
				POS: function(a, b, c, d) {
					var e = b[2],
						f = o.setFilters[e];
					return f ? f(a, c, b, d) : void 0
				}
			}
		}, p = o.match.POS, q = function(a, b) {
			return "\\" + (b - 0 + 1)
		};
		for (r in o.match) o.match[r] = new RegExp(o.match[r].source + /(?![^\[]*\])(?![^\(]*\))/.source), o.leftMatch[r] = new RegExp(/(^(?:.|\r|\n)*?)/.source + o.match[r].source.replace(/\\(\d+)/g, q));
		o.match.globalPOS = p, s = function(a, b) {
			return a = Array.prototype.slice.call(a, 0), b ? (b.push.apply(b, a), b) : a
		};
		try {
			Array.prototype.slice.call(G.documentElement.childNodes, 0)[0].nodeType
		} catch (t) {
			s = function(a, b) {
				var e, c = 0,
					d = b || [];
				if ("[object Array]" === g.call(a)) Array.prototype.push.apply(d, a);
				else if ("number" == typeof a.length) for (e = a.length; e > c; c++) d.push(a[c]);
				else for (; a[c]; c++) d.push(a[c]);
				return d
			}
		}
		G.documentElement.compareDocumentPosition ? u = function(a, b) {
			return a === b ? (h = !0, 0) : a.compareDocumentPosition && b.compareDocumentPosition ? 4 & a.compareDocumentPosition(b) ? -1 : 1 : a.compareDocumentPosition ? -1 : 1
		} : (u = function(a, b) {
			var c, d, e, f, g, i, j, k;
			if (a === b) return h = !0, 0;
			if (a.sourceIndex && b.sourceIndex) return a.sourceIndex - b.sourceIndex;
			if (e = [], f = [], g = a.parentNode, i = b.parentNode, j = g, g === i) return v(a, b);
			if (!g) return -1;
			if (!i) return 1;
			for (; j;) e.unshift(j), j = j.parentNode;
			for (j = i; j;) f.unshift(j), j = j.parentNode;
			for (c = e.length, d = f.length, k = 0; c > k && d > k; k++) if (e[k] !== f[k]) return v(e[k], f[k]);
			return k === c ? v(a, f[k], -1) : v(e[k], b, 1)
		}, v = function(a, b, c) {
			if (a === b) return c;
			for (var d = a.nextSibling; d;) {
				if (d === b) return -1;
				d = d.nextSibling
			}
			return 1
		}), function() {
			var a = G.createElement("div"),
				c = "script" + (new Date).getTime(),
				d = G.documentElement;
			a.innerHTML = "<a name='" + c + "'/>", d.insertBefore(a, d.firstChild), G.getElementById(c) && (o.find.ID = function(a, c, d) {
				if ("undefined" != typeof c.getElementById && !d) {
					var e = c.getElementById(a[1]);
					return e ? e.id === a[1] || "undefined" != typeof e.getAttributeNode && e.getAttributeNode("id").nodeValue === a[1] ? [e] : b : []
				}
			}, o.filter.ID = function(a, b) {
				var c = "undefined" != typeof a.getAttributeNode && a.getAttributeNode("id");
				return 1 === a.nodeType && c && c.nodeValue === b
			}), d.removeChild(a), d = a = null
		}(), function() {
			var a = G.createElement("div");
			a.appendChild(G.createComment("")), a.getElementsByTagName("*").length > 0 && (o.find.TAG = function(a, b) {
				var d, e, c = b.getElementsByTagName(a[1]);
				if ("*" === a[1]) {
					for (d = [], e = 0; c[e]; e++) 1 === c[e].nodeType && d.push(c[e]);
					c = d
				}
				return c
			}), a.innerHTML = "<a href='#'></a>", a.firstChild && "undefined" != typeof a.firstChild.getAttribute && "#" !== a.firstChild.getAttribute("href") && (o.attrHandle.href = function(a) {
				return a.getAttribute("href", 2)
			}), a = null
		}(), G.querySelectorAll &&
		function() {
			var d, a = m,
				b = G.createElement("div"),
				c = "__sizzle__";
			if (b.innerHTML = "<p class='TEST'></p>", !b.querySelectorAll || 0 !== b.querySelectorAll(".TEST").length) {
				m = function(b, d, e, f) {
					var g, h, j, k, l, n, p;
					if (d = d || G, !f && !m.isXML(d)) {
						if (g = /^(\w+$)|^\.([\w\-]+$)|^#([\w\-]+$)/.exec(b), g && (1 === d.nodeType || 9 === d.nodeType)) {
							if (g[1]) return s(d.getElementsByTagName(b), e);
							if (g[2] && o.find.CLASS && d.getElementsByClassName) return s(d.getElementsByClassName(g[2]), e)
						}
						if (9 === d.nodeType) {
							if ("body" === b && d.body) return s([d.body], e);
							if (g && g[3]) {
								if (h = d.getElementById(g[3]), !h || !h.parentNode) return s([], e);
								if (h.id === g[3]) return s([h], e)
							}
							try {
								return s(d.querySelectorAll(b), e)
							} catch (i) {}
						} else if (1 === d.nodeType && "object" !== d.nodeName.toLowerCase()) {
							j = d, k = d.getAttribute("id"), l = k || c, n = d.parentNode, p = /^\s*[+~]/.test(b), k ? l = l.replace(/'/g, "\\$&") : d.setAttribute("id", l), p && n && (d = d.parentNode);
							try {
								if (!p || n) return s(d.querySelectorAll("[id='" + l + "'] " + b), e)
							} catch (q) {} finally {
								k || j.removeAttribute("id")
							}
						}
					}
					return a(b, d, e, f)
				};
				for (d in a) m[d] = a[d];
				b = null
			}
		}(), function() {
			var c, d, a = G.documentElement,
				b = a.matchesSelector || a.mozMatchesSelector || a.webkitMatchesSelector || a.msMatchesSelector;
			if (b) {
				c = !b.call(G.createElement("div"), "div"), d = !1;
				try {
					b.call(G.documentElement, "[test!='']:sizzle")
				} catch (e) {
					d = !0
				}
				m.matchesSelector = function(a, e) {
					if (e = e.replace(/\=\s*([^'"\]]*)\s*\]/g, "='$1']"), !m.isXML(a)) try {
						if (d || !o.match.PSEUDO.test(e) && !/!=/.test(e)) {
							var f = b.call(a, e);
							if (f || !c || a.document && 11 !== a.document.nodeType) return f
						}
					} catch (g) {}
					return m(e, null, null, [a]).length > 0
				}
			}
		}(), function() {
			var a = G.createElement("div");
			if (a.innerHTML = "<div class='test e'></div><div class='test'></div>", a.getElementsByClassName && 0 !== a.getElementsByClassName("e").length) {
				if (a.lastChild.className = "e", 1 === a.getElementsByClassName("e").length) return;
				o.order.splice(1, 0, "CLASS"), o.find.CLASS = function(a, b, c) {
					return "undefined" == typeof b.getElementsByClassName || c ? void 0 : b.getElementsByClassName(a[1])
				}, a = null
			}
		}(), m.contains = G.documentElement.contains ?
		function(a, b) {
			return a !== b && (a.contains ? a.contains(b) : !0)
		} : G.documentElement.compareDocumentPosition ?
		function(a, b) {
			return !!(16 & a.compareDocumentPosition(b))
		} : function() {
			return !1
		}, m.isXML = function(a) {
			var b = (a ? a.ownerDocument || a : 0).documentElement;
			return b ? "HTML" !== b.nodeName : !1
		}, w = function(a, b, c) {
			for (var d, h, i, e = [], f = "", g = b.nodeType ? [b] : b; d = o.match.PSEUDO.exec(a);) f += d[0], a = a.replace(o.match.PSEUDO, "");
			for (a = o.relative[a] ? a + "*" : a, h = 0, i = g.length; i > h; h++) m(a, g[h], e, c);
			return m.filter(f, e)
		}, m.attr = J.attr, m.selectors.attrMap = {}, J.find = m, J.expr = m.selectors, J.expr[":"] = J.expr.filters, J.unique = m.uniqueSort, J.text = m.getText, J.isXMLDoc = m.isXML, J.contains = m.contains
	}(), hb = /Until$/, ib = /^(?:parents|prevUntil|prevAll)/, jb = /,/, kb = /^.[^:#\[\.,]*$/, lb = Array.prototype.slice, mb = J.expr.match.globalPOS, nb = {
		children: !0,
		contents: !0,
		next: !0,
		prev: !0
	}, J.fn.extend({
		find: function(a) {
			var c, d, f, g, h, e, b = this;
			if ("string" != typeof a) return J(a).filter(function() {
				for (c = 0, d = b.length; d > c; c++) if (J.contains(b[c], this)) return !0
			});
			for (e = this.pushStack("", "find", a), c = 0, d = this.length; d > c; c++) if (f = e.length, J.find(a, this[c], e), c > 0) for (g = f; g < e.length; g++) for (h = 0; f > h; h++) if (e[h] === e[g]) {
				e.splice(g--, 1);
				break
			}
			return e
		},
		has: function(a) {
			var b = J(a);
			return this.filter(function() {
				for (var a = 0, c = b.length; c > a; a++) if (J.contains(this, b[a])) return !0
			})
		},
		not: function(a) {
			return this.pushStack(y(this, a, !1), "not", a)
		},
		filter: function(a) {
			return this.pushStack(y(this, a, !0), "filter", a)
		},
		is: function(a) {
			return !!a && ("string" == typeof a ? mb.test(a) ? J(a, this.context).index(this[0]) >= 0 : J.filter(a, this).length > 0 : this.filter(a).length > 0)
		},
		closest: function(a, b) {
			var d, e, g, h, c = [],
				f = this[0];
			if (J.isArray(a)) {
				for (g = 1; f && f.ownerDocument && f !== b;) {
					for (d = 0; d < a.length; d++) J(f).is(a[d]) && c.push({
						selector: a[d],
						elem: f,
						level: g
					});
					f = f.parentNode, g++
				}
				return c
			}
			for (h = mb.test(a) || "string" != typeof a ? J(a, b || this.context) : 0, d = 0, e = this.length; e > d; d++) for (f = this[d]; f;) {
				if (h ? h.index(f) > -1 : J.find.matchesSelector(f, a)) {
					c.push(f);
					break
				}
				if (f = f.parentNode, !f || !f.ownerDocument || f === b || 11 === f.nodeType) break
			}
			return c = c.length > 1 ? J.unique(c) : c, this.pushStack(c, "closest", a)
		},
		index: function(a) {
			return a ? "string" == typeof a ? J.inArray(this[0], J(a)) : J.inArray(a.jquery ? a[0] : a, this) : this[0] && this[0].parentNode ? this.prevAll().length : -1
		},
		add: function(a, b) {
			var c = "string" == typeof a ? J(a, b) : J.makeArray(a && a.nodeType ? [a] : a),
				d = J.merge(this.get(), c);
			return this.pushStack(z(c[0]) || z(d[0]) ? d : J.unique(d))
		},
		andSelf: function() {
			return this.add(this.prevObject)
		}
	}), J.each({
		parent: function(a) {
			var b = a.parentNode;
			return b && 11 !== b.nodeType ? b : null
		},
		parents: function(a) {
			return J.dir(a, "parentNode")
		},
		parentsUntil: function(a, b, c) {
			return J.dir(a, "parentNode", c)
		},
		next: function(a) {
			return J.nth(a, 2, "nextSibling")
		},
		prev: function(a) {
			return J.nth(a, 2, "previousSibling")
		},
		nextAll: function(a) {
			return J.dir(a, "nextSibling")
		},
		prevAll: function(a) {
			return J.dir(a, "previousSibling")
		},
		nextUntil: function(a, b, c) {
			return J.dir(a, "nextSibling", c)
		},
		prevUntil: function(a, b, c) {
			return J.dir(a, "previousSibling", c)
		},
		siblings: function(a) {
			return J.sibling((a.parentNode || {}).firstChild, a)
		},
		children: function(a) {
			return J.sibling(a.firstChild)
		},
		contents: function(a) {
			return J.nodeName(a, "iframe") ? a.contentDocument || a.contentWindow.document : J.makeArray(a.childNodes)
		}
	}, function(a, b) {
		J.fn[a] = function(c, d) {
			var e = J.map(this, b, c);
			return hb.test(a) || (d = c), d && "string" == typeof d && (e = J.filter(d, e)), e = this.length > 1 && !nb[a] ? J.unique(e) : e, (this.length > 1 || jb.test(d)) && ib.test(a) && (e = e.reverse()), this.pushStack(e, a, lb.call(arguments).join(","))
		}
	}), J.extend({
		filter: function(a, b, c) {
			return c && (a = ":not(" + a + ")"), 1 === b.length ? J.find.matchesSelector(b[0], a) ? [b[0]] : [] : J.find.matches(a, b)
		},
		dir: function(a, c, d) {
			for (var e = [], f = a[c]; f && 9 !== f.nodeType && (d === b || 1 !== f.nodeType || !J(f).is(d));) 1 === f.nodeType && e.push(f), f = f[c];
			return e
		},
		nth: function(a, b, c) {
			b = b || 1;
			for (var e = 0; a && (1 !== a.nodeType || ++e !== b); a = a[c]);
			return a
		},
		sibling: function(a, b) {
			for (var c = []; a; a = a.nextSibling) 1 === a.nodeType && a !== b && c.push(a);
			return c
		}
	}), ob = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video", pb = / jQuery\d+="(?:\d+|null)"/g, qb = /^\s+/, rb = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi, sb = /<([\w:]+)/, tb = /<tbody/i, ub = /<|&#?\w+;/, vb = /<(?:script|style)/i, wb = /<(?:script|object|embed|option|style)/i, xb = new RegExp("<(?:" + ob + ")[\\s/>]", "i"), yb = /checked\s*(?:[^=]|=\s*.checked.)/i, zb = /\/(java|ecma)script/i, Ab = /^\s*<!(?:\[CDATA\[|\-\-)/, Bb = {
		option: [1, "<select multiple='multiple'>", "</select>"],
		legend: [1, "<fieldset>", "</fieldset>"],
		thead: [1, "<table>", "</table>"],
		tr: [2, "<table><tbody>", "</tbody></table>"],
		td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
		col: [2, "<table><tbody></tbody><colgroup>", "</colgroup></table>"],
		area: [1, "<map>", "</map>"],
		_default: [0, "", ""]
	}, Cb = x(G), Bb.optgroup = Bb.option, Bb.tbody = Bb.tfoot = Bb.colgroup = Bb.caption = Bb.thead, Bb.th = Bb.td, J.support.htmlSerialize || (Bb._default = [1, "div<div>", "</div>"]), J.fn.extend({
		text: function(a) {
			return J.access(this, function(a) {
				return a === b ? J.text(this) : this.empty().append((this[0] && this[0].ownerDocument || G).createTextNode(a))
			}, null, a, arguments.length)
		},
		wrapAll: function(a) {
			if (J.isFunction(a)) return this.each(function(b) {
				J(this).wrapAll(a.call(this, b))
			});
			if (this[0]) {
				var b = J(a, this[0].ownerDocument).eq(0).clone(!0);
				this[0].parentNode && b.insertBefore(this[0]), b.map(function() {
					for (var a = this; a.firstChild && 1 === a.firstChild.nodeType;) a = a.firstChild;
					return a
				}).append(this)
			}
			return this
		},
		wrapInner: function(a) {
			return J.isFunction(a) ? this.each(function(b) {
				J(this).wrapInner(a.call(this, b))
			}) : this.each(function() {
				var b = J(this),
					c = b.contents();
				c.length ? c.wrapAll(a) : b.append(a)
			})
		},
		wrap: function(a) {
			var b = J.isFunction(a);
			return this.each(function(c) {
				J(this).wrapAll(b ? a.call(this, c) : a)
			})
		},
		unwrap: function() {
			return this.parent().each(function() {
				J.nodeName(this, "body") || J(this).replaceWith(this.childNodes)
			}).end()
		},
		append: function() {
			return this.domManip(arguments, !0, function(a) {
				1 === this.nodeType && this.appendChild(a)
			})
		},
		prepend: function() {
			return this.domManip(arguments, !0, function(a) {
				1 === this.nodeType && this.insertBefore(a, this.firstChild)
			})
		},
		before: function() {
			if (this[0] && this[0].parentNode) return this.domManip(arguments, !1, function(a) {
				this.parentNode.insertBefore(a, this)
			});
			if (arguments.length) {
				var a = J.clean(arguments);
				return a.push.apply(a, this.toArray()), this.pushStack(a, "before", arguments)
			}
		},
		after: function() {
			if (this[0] && this[0].parentNode) return this.domManip(arguments, !1, function(a) {
				this.parentNode.insertBefore(a, this.nextSibling)
			});
			if (arguments.length) {
				var a = this.pushStack(this, "after", arguments);
				return a.push.apply(a, J.clean(arguments)), a
			}
		},
		remove: function(a, b) {
			for (var d, c = 0; null != (d = this[c]); c++)(!a || J.filter(a, [d]).length) && (!b && 1 === d.nodeType && (J.cleanData(d.getElementsByTagName("*")), J.cleanData([d])), d.parentNode && d.parentNode.removeChild(d));
			return this
		},
		empty: function() {
			for (var b, a = 0; null != (b = this[a]); a++) for (1 === b.nodeType && J.cleanData(b.getElementsByTagName("*")); b.firstChild;) b.removeChild(b.firstChild);
			return this
		},
		clone: function(a, b) {
			return a = null == a ? !1 : a, b = null == b ? a : b, this.map(function() {
				return J.clone(this, a, b)
			})
		},
		html: function(a) {
			return J.access(this, function(a) {
				var c = this[0] || {},
					d = 0,
					e = this.length;
				if (a === b) return 1 === c.nodeType ? c.innerHTML.replace(pb, "") : null;
				if (!("string" != typeof a || vb.test(a) || !J.support.leadingWhitespace && qb.test(a) || Bb[(sb.exec(a) || ["", ""])[1].toLowerCase()])) {
					a = a.replace(rb, "<$1></$2>");
					try {
						for (; e > d; d++) c = this[d] || {}, 1 === c.nodeType && (J.cleanData(c.getElementsByTagName("*")), c.innerHTML = a);
						c = 0
					} catch (f) {}
				}
				c && this.empty().append(a)
			}, null, a, arguments.length)
		},
		replaceWith: function(a) {
			return this[0] && this[0].parentNode ? J.isFunction(a) ? this.each(function(b) {
				var c = J(this),
					d = c.html();
				c.replaceWith(a.call(this, b, d))
			}) : ("string" != typeof a && (a = J(a).detach()), this.each(function() {
				var b = this.nextSibling,
					c = this.parentNode;
				J(this).remove(), b ? J(b).before(a) : J(c).append(a)
			})) : this.length ? this.pushStack(J(J.isFunction(a) ? a() : a), "replaceWith", a) : this
		},
		detach: function(a) {
			return this.remove(a, !0)
		},
		domManip: function(a, c, d) {
			var e, f, g, h, k, l, m, i = a[0],
				j = [];
			if (!J.support.checkClone && 3 === arguments.length && "string" == typeof i && yb.test(i)) return this.each(function() {
				J(this).domManip(a, c, d, !0)
			});
			if (J.isFunction(i)) return this.each(function(e) {
				var f = J(this);
				a[0] = i.call(this, e, c ? f.html() : b), f.domManip(a, c, d)
			});
			if (this[0]) {
				if (h = i && i.parentNode, e = J.support.parentNode && h && 11 === h.nodeType && h.childNodes.length === this.length ? {
					fragment: h
				} : J.buildFragment(a, this, j), g = e.fragment, f = 1 === g.childNodes.length ? g = g.firstChild : g.firstChild, f) for (c = c && J.nodeName(f, "tr"), k = 0, l = this.length, m = l - 1; l > k; k++) d.call(c ? w(this[k], f) : this[k], e.cacheable || l > 1 && m > k ? J.clone(g, !0, !0) : g);
				j.length && J.each(j, function(a, b) {
					b.src ? J.ajax({
						type: "GET",
						global: !1,
						url: b.src,
						async: !1,
						dataType: "script"
					}) : J.globalEval((b.text || b.textContent || b.innerHTML || "").replace(Ab, "/*$0*/")), b.parentNode && b.parentNode.removeChild(b)
				})
			}
			return this
		}
	}), J.buildFragment = function(a, b, c) {
		var d, e, f, g, h = a[0];
		return b && b[0] && (g = b[0].ownerDocument || b[0]), g.createDocumentFragment || (g = G), 1 === a.length && "string" == typeof h && h.length < 512 && g === G && "<" === h.charAt(0) && !wb.test(h) && (J.support.checkClone || !yb.test(h)) && (J.support.html5Clone || !xb.test(h)) && (e = !0, f = J.fragments[h], f && 1 !== f && (d = f)), d || (d = g.createDocumentFragment(), J.clean(a, g, d, c)), e && (J.fragments[h] = f ? d : 1), {
			fragment: d,
			cacheable: e
		}
	}, J.fragments = {}, J.each({
		appendTo: "append",
		prependTo: "prepend",
		insertBefore: "before",
		insertAfter: "after",
		replaceAll: "replaceWith"
	}, function(a, b) {
		J.fn[a] = function(c) {
			var g, h, i, d = [],
				e = J(c),
				f = 1 === this.length && this[0].parentNode;
			if (f && 11 === f.nodeType && 1 === f.childNodes.length && 1 === e.length) return e[b](this[0]), this;
			for (g = 0, h = e.length; h > g; g++) i = (g > 0 ? this.clone(!0) : this).get(), J(e[g])[b](i), d = d.concat(i);
			return this.pushStack(d, a, e.selector)
		}
	}), J.extend({
		clone: function(a, b, c) {
			var d, e, f, g = J.support.html5Clone || J.isXMLDoc(a) || !xb.test("<" + a.nodeName + ">") ? a.cloneNode(!0) : q(a);
			if (!(J.support.noCloneEvent && J.support.noCloneChecked || 1 !== a.nodeType && 11 !== a.nodeType || J.isXMLDoc(a))) for (u(a, g), d = t(a), e = t(g), f = 0; d[f]; ++f) e[f] && u(d[f], e[f]);
			if (b && (v(a, g), c)) for (d = t(a), e = t(g), f = 0; d[f]; ++f) v(d[f], e[f]);
			return d = e = null, g
		},
		clean: function(a, b, c, d) {
			var e, f, g, j, i, p, k, l, m, n, o, q, s, t, u, h = [];
			for (b = b || G, "undefined" == typeof b.createElement && (b = b.ownerDocument || b[0] && b[0].ownerDocument || G), i = 0; null != (j = a[i]); i++) if ("number" == typeof j && (j += ""), j) {
				if ("string" == typeof j) if (ub.test(j)) {
					for (j = j.replace(rb, "<$1></$2>"), k = (sb.exec(j) || ["", ""])[1].toLowerCase(), l = Bb[k] || Bb._default, m = l[0], n = b.createElement("div"), o = Cb.childNodes, b === G ? Cb.appendChild(n) : x(b).appendChild(n), n.innerHTML = l[1] + j + l[2]; m--;) n = n.lastChild;
					if (!J.support.tbody) for (q = tb.test(j), s = "table" !== k || q ? "<table>" !== l[1] || q ? [] : n.childNodes : n.firstChild && n.firstChild.childNodes, g = s.length - 1; g >= 0; --g) J.nodeName(s[g], "tbody") && !s[g].childNodes.length && s[g].parentNode.removeChild(s[g]);
					!J.support.leadingWhitespace && qb.test(j) && n.insertBefore(b.createTextNode(qb.exec(j)[0]), n.firstChild), j = n.childNodes, n && (n.parentNode.removeChild(n), o.length > 0 && (p = o[o.length - 1], p && p.parentNode && p.parentNode.removeChild(p)))
				} else j = b.createTextNode(j);
				if (!J.support.appendChecked) if (j[0] && "number" == typeof(t = j.length)) for (g = 0; t > g; g++) r(j[g]);
				else r(j);
				j.nodeType ? h.push(j) : h = J.merge(h, j)
			}
			if (c) for (e = function(a) {
				return !a.type || zb.test(a.type)
			}, i = 0; h[i]; i++) f = h[i], d && J.nodeName(f, "script") && (!f.type || zb.test(f.type)) ? d.push(f.parentNode ? f.parentNode.removeChild(f) : f) : (1 === f.nodeType && (u = J.grep(f.getElementsByTagName("script"), e), h.splice.apply(h, [i + 1, 0].concat(u))), c.appendChild(f));
			return h
		},
		cleanData: function(a) {
			var b, c, h, g, i, d = J.cache,
				e = J.event.special,
				f = J.support.deleteExpando;
			for (g = 0; null != (h = a[g]); g++) if ((!h.nodeName || !J.noData[h.nodeName.toLowerCase()]) && (c = h[J.expando])) {
				if (b = d[c], b && b.events) {
					for (i in b.events) e[i] ? J.event.remove(h, i) : J.removeEvent(h, i, b.handle);
					b.handle && (b.handle.elem = null)
				}
				f ? delete h[J.expando] : h.removeAttribute && h.removeAttribute(J.expando), delete d[c]
			}
		}
	}), Db = /alpha\([^)]*\)/i, Eb = /opacity=([^)]*)/, Fb = /([A-Z]|^ms)/g, Gb = /^[\-+]?(?:\d*\.)?\d+$/i, Hb = /^-?(?:\d*\.)?\d+(?!px)[^\d\s]+$/i, Ib = /^([\-+])=([\-+.\de]+)/, Jb = /^margin/, Kb = {
		position: "absolute",
		visibility: "hidden",
		display: "block"
	}, Lb = ["Top", "Right", "Bottom", "Left"], J.fn.css = function(a, c) {
		return J.access(this, function(a, c, d) {
			return d !== b ? J.style(a, c, d) : J.css(a, c)
		}, a, c, arguments.length > 1)
	}, J.extend({
		cssHooks: {
			opacity: {
				get: function(a, b) {
					if (b) {
						var c = Mb(a, "opacity");
						return "" === c ? "1" : c
					}
					return a.style.opacity
				}
			}
		},
		cssNumber: {
			fillOpacity: !0,
			fontWeight: !0,
			lineHeight: !0,
			opacity: !0,
			orphans: !0,
			widows: !0,
			zIndex: !0,
			zoom: !0
		},
		cssProps: {
			"float": J.support.cssFloat ? "cssFloat" : "styleFloat"
		},
		style: function(a, c, d, e) {
			if (a && 3 !== a.nodeType && 8 !== a.nodeType && a.style) {
				var f, g, h = J.camelCase(c),
					i = a.style,
					j = J.cssHooks[h];
				if (c = J.cssProps[h] || h, d === b) return j && "get" in j && (f = j.get(a, !1, e)) !== b ? f : i[c];
				if (g = typeof d, "string" === g && (f = Ib.exec(d)) && (d = +(f[1] + 1) * +f[2] + parseFloat(J.css(a, c)), g = "number"), null == d || "number" === g && isNaN(d)) return;
				if ("number" === g && !J.cssNumber[h] && (d += "px"), !(j && "set" in j && (d = j.set(a, d)) === b)) try {
					i[c] = d
				} catch (k) {}
			}
		},
		css: function(a, c, d) {
			var e, f;
			return c = J.camelCase(c), f = J.cssHooks[c], c = J.cssProps[c] || c, "cssFloat" === c && (c = "float"), f && "get" in f && (e = f.get(a, !0, d)) !== b ? e : Mb ? Mb(a, c) : void 0
		},
		swap: function(a, b, c) {
			var e, f, d = {};
			for (f in b) d[f] = a.style[f], a.style[f] = b[f];
			e = c.call(a);
			for (f in b) a.style[f] = d[f];
			return e
		}
	}), J.curCSS = J.css, G.defaultView && G.defaultView.getComputedStyle && (Nb = function(a, b) {
		var c, d, e, f, g = a.style;
		return b = b.replace(Fb, "-$1").toLowerCase(), (d = a.ownerDocument.defaultView) && (e = d.getComputedStyle(a, null)) && (c = e.getPropertyValue(b), "" === c && !J.contains(a.ownerDocument.documentElement, a) && (c = J.style(a, b))), !J.support.pixelMargin && e && Jb.test(b) && Hb.test(c) && (f = g.width, g.width = c, c = e.width, g.width = f), c
	}), G.documentElement.currentStyle && (Ob = function(a, b) {
		var c, d, e, f = a.currentStyle && a.currentStyle[b],
			g = a.style;
		return null == f && g && (e = g[b]) && (f = e), Hb.test(f) && (c = g.left, d = a.runtimeStyle && a.runtimeStyle.left, d && (a.runtimeStyle.left = a.currentStyle.left), g.left = "fontSize" === b ? "1em" : f, f = g.pixelLeft + "px", g.left = c, d && (a.runtimeStyle.left = d)), "" === f ? "auto" : f
	}), Mb = Nb || Ob, J.each(["height", "width"], function(a, b) {
		J.cssHooks[b] = {
			get: function(a, c, d) {
				return c ? 0 !== a.offsetWidth ? p(a, b, d) : J.swap(a, Kb, function() {
					return p(a, b, d)
				}) : void 0
			},
			set: function(a, b) {
				return Gb.test(b) ? b + "px" : b
			}
		}
	}), J.support.opacity || (J.cssHooks.opacity = {
		get: function(a, b) {
			return Eb.test((b && a.currentStyle ? a.currentStyle.filter : a.style.filter) || "") ? parseFloat(RegExp.$1) / 100 + "" : b ? "1" : ""
		},
		set: function(a, b) {
			var c = a.style,
				d = a.currentStyle,
				e = J.isNumeric(b) ? "alpha(opacity=" + 100 * b + ")" : "",
				f = d && d.filter || c.filter || "";
			c.zoom = 1, b >= 1 && "" === J.trim(f.replace(Db, "")) && (c.removeAttribute("filter"), d && !d.filter) || (c.filter = Db.test(f) ? f.replace(Db, e) : f + " " + e)
		}
	}), J(function() {
		J.support.reliableMarginRight || (J.cssHooks.marginRight = {
			get: function(a, b) {
				return J.swap(a, {
					display: "inline-block"
				}, function() {
					return b ? Mb(a, "margin-right") : a.style.marginRight
				})
			}
		})
	}), J.expr && J.expr.filters && (J.expr.filters.hidden = function(a) {
		var b = a.offsetWidth,
			c = a.offsetHeight;
		return 0 === b && 0 === c || !J.support.reliableHiddenOffsets && "none" === (a.style && a.style.display || J.css(a, "display"))
	}, J.expr.filters.visible = function(a) {
		return !J.expr.filters.hidden(a)
	}), J.each({
		margin: "",
		padding: "",
		border: "Width"
	}, function(a, b) {
		J.cssHooks[a + b] = {
			expand: function(c) {
				var d, e = "string" == typeof c ? c.split(" ") : [c],
					f = {};
				for (d = 0; 4 > d; d++) f[a + Lb[d] + b] = e[d] || e[d - 2] || e[0];
				return f
			}
		}
	}), Pb = /%20/g, Qb = /\[\]$/, Rb = /\r?\n/g, Sb = /#.*$/, Tb = /^(.*?):[ \t]*([^\r\n]*)\r?$/gm, Ub = /^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i, Vb = /^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/, Wb = /^(?:GET|HEAD)$/, Xb = /^\/\//, Yb = /\?/, Zb = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, $b = /^(?:select|textarea)/i, _b = /\s+/, ac = /([?&])_=[^&]*/, bc = /^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+))?)?/, cc = J.fn.load, dc = {}, ec = {}, hc = ["*/"] + ["*"];
	try {
		fc = I.href
	} catch (ic) {
		fc = G.createElement("a"), fc.href = "", fc = fc.href
	}
	gc = bc.exec(fc.toLowerCase()) || [], J.fn.extend({
		load: function(a, c, d) {
			var e, f, g, h;
			return "string" != typeof a && cc ? cc.apply(this, arguments) : this.length ? (e = a.indexOf(" "), e >= 0 && (f = a.slice(e, a.length), a = a.slice(0, e)), g = "GET", c && (J.isFunction(c) ? (d = c, c = b) : "object" == typeof c && (c = J.param(c, J.ajaxSettings.traditional), g = "POST")), h = this, J.ajax({
				url: a,
				type: g,
				dataType: "html",
				data: c,
				complete: function(a, b, c) {
					c = a.responseText, a.isResolved() && (a.done(function(a) {
						c = a
					}), h.html(f ? J("<div>").append(c.replace(Zb, "")).find(f) : c)), d && h.each(d, [c, b, a])
				}
			}), this) : this
		},
		serialize: function() {
			return J.param(this.serializeArray())
		},
		serializeArray: function() {
			return this.map(function() {
				return this.elements ? J.makeArray(this.elements) : this
			}).filter(function() {
				return this.name && !this.disabled && (this.checked || $b.test(this.nodeName) || Ub.test(this.type))
			}).map(function(a, b) {
				var c = J(this).val();
				return null == c ? null : J.isArray(c) ? J.map(c, function(a) {
					return {
						name: b.name,
						value: a.replace(Rb, "\r\n")
					}
				}) : {
					name: b.name,
					value: c.replace(Rb, "\r\n")
				}
			}).get()
		}
	}), J.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "), function(a, b) {
		J.fn[b] = function(a) {
			return this.on(b, a)
		}
	}), J.each(["get", "post"], function(a, c) {
		J[c] = function(a, d, e, f) {
			return J.isFunction(d) && (f = f || e, e = d, d = b), J.ajax({
				type: c,
				url: a,
				data: d,
				success: e,
				dataType: f
			})
		}
	}), J.extend({
		getScript: function(a, c) {
			return J.get(a, b, c, "script")
		},
		getJSON: function(a, b, c) {
			return J.get(a, b, c, "json")
		},
		ajaxSetup: function(a, b) {
			return b ? m(a, J.ajaxSettings) : (b = a, a = J.ajaxSettings), m(a, b), a
		},
		ajaxSettings: {
			url: fc,
			isLocal: Vb.test(gc[1]),
			global: !0,
			type: "GET",
			contentType: "application/x-www-form-urlencoded; charset=UTF-8",
			processData: !0,
			async: !0,
			accepts: {
				xml: "application/xml, text/xml",
				html: "text/html",
				text: "text/plain",
				json: "application/json, text/javascript",
				"*": hc
			},
			contents: {
				xml: /xml/,
				html: /html/,
				json: /json/
			},
			responseFields: {
				xml: "responseXML",
				text: "responseText"
			},
			converters: {
				"* text": a.String,
				"text html": !0,
				"text json": J.parseJSON,
				"text xml": J.parseXML
			},
			flatOptions: {
				context: !0,
				url: !0
			}
		},
		ajaxPrefilter: o(dc),
		ajaxTransport: o(ec),
		ajax: function(a, c) {
			function d(a, c, d, n) {
				if (2 !== v) {
					v = 2, t && clearTimeout(t), s = b, q = n || "", y.readyState = a > 0 ? 4 : 0;
					var o, p, r, z, A, u = c,
						x = d ? k(e, y, d) : b;
					if (a >= 200 && 300 > a || 304 === a) if (e.ifModified && ((z = y.getResponseHeader("Last-Modified")) && (J.lastModified[m] = z), (A = y.getResponseHeader("Etag")) && (J.etag[m] = A)), 304 === a) u = "notmodified", o = !0;
					else try {
						p = j(e, x), u = "success", o = !0
					} catch (B) {
						u = "parsererror", r = B
					} else r = u, (!u || a) && (u = "error", 0 > a && (a = 0));
					y.status = a, y.statusText = "" + (c || u), o ? h.resolveWith(f, [p, u, y]) : h.rejectWith(f, [y, u, r]), y.statusCode(l), l = b, w && g.trigger("ajax" + (o ? "Success" : "Error"), [y, e, o ? p : r]), i.fireWith(f, [y, u]), w && (g.trigger("ajaxComplete", [y, e]), --J.active || J.event.trigger("ajaxStop"))
				}
			}
			var m, q, r, s, t, u, w, x, e, f, g, h, i, l, o, p, v, y, z, A;
			if ("object" == typeof a && (c = a, a = b), c = c || {}, e = J.ajaxSetup({}, c), f = e.context || e, g = f !== e && (f.nodeType || f instanceof J) ? J(f) : J.event, h = J.Deferred(), i = J.Callbacks("once memory"), l = e.statusCode || {}, o = {}, p = {}, v = 0, y = {
				readyState: 0,
				setRequestHeader: function(a, b) {
					if (!v) {
						var c = a.toLowerCase();
						a = p[c] = p[c] || a, o[a] = b
					}
					return this
				},
				getAllResponseHeaders: function() {
					return 2 === v ? q : null
				},
				getResponseHeader: function(a) {
					var c;
					if (2 === v) {
						if (!r) for (r = {}; c = Tb.exec(q);) r[c[1].toLowerCase()] = c[2];
						c = r[a.toLowerCase()]
					}
					return c === b ? null : c
				},
				overrideMimeType: function(a) {
					return v || (e.mimeType = a), this
				},
				abort: function(a) {
					return a = a || "abort", s && s.abort(a), d(0, a), this
				}
			}, h.promise(y), y.success = y.done, y.error = y.fail, y.complete = i.add, y.statusCode = function(a) {
				if (a) {
					var b;
					if (2 > v) for (b in a) l[b] = [l[b], a[b]];
					else b = a[y.status], y.then(b, b)
				}
				return this
			}, e.url = ((a || e.url) + "").replace(Sb, "").replace(Xb, gc[1] + "//"), e.dataTypes = J.trim(e.dataType || "*").toLowerCase().split(_b), null == e.crossDomain && (u = bc.exec(e.url.toLowerCase()), e.crossDomain = !(!u || u[1] == gc[1] && u[2] == gc[2] && (u[3] || ("http:" === u[1] ? 80 : 443)) == (gc[3] || ("http:" === gc[1] ? 80 : 443)))), e.data && e.processData && "string" != typeof e.data && (e.data = J.param(e.data, e.traditional)), n(dc, e, c, y), 2 === v) return !1;
			w = e.global, e.type = e.type.toUpperCase(), e.hasContent = !Wb.test(e.type), w && 0 === J.active++ && J.event.trigger("ajaxStart"), e.hasContent || (e.data && (e.url += (Yb.test(e.url) ? "&" : "?") + e.data, delete e.data), m = e.url, e.cache === !1 && (z = J.now(), A = e.url.replace(ac, "$1_=" + z), e.url = A + (A === e.url ? (Yb.test(e.url) ? "&" : "?") + "_=" + z : ""))), (e.data && e.hasContent && e.contentType !== !1 || c.contentType) && y.setRequestHeader("Content-Type", e.contentType), e.ifModified && (m = m || e.url, J.lastModified[m] && y.setRequestHeader("If-Modified-Since", J.lastModified[m]), J.etag[m] && y.setRequestHeader("If-None-Match", J.etag[m])), y.setRequestHeader("Accept", e.dataTypes[0] && e.accepts[e.dataTypes[0]] ? e.accepts[e.dataTypes[0]] + ("*" !== e.dataTypes[0] ? ", " + hc + "; q=0.01" : "") : e.accepts["*"]);
			for (x in e.headers) y.setRequestHeader(x, e.headers[x]);
			if (e.beforeSend && (e.beforeSend.call(f, y, e) === !1 || 2 === v)) return y.abort(), !1;
			for (x in {
				success: 1,
				error: 1,
				complete: 1
			}) y[x](e[x]);
			if (s = n(ec, e, c, y)) {
				y.readyState = 1, w && g.trigger("ajaxSend", [y, e]), e.async && e.timeout > 0 && (t = setTimeout(function() {
					y.abort("timeout")
				}, e.timeout));
				try {
					v = 1, s.send(o, d)
				} catch (B) {
					if (!(2 > v)) throw B;
					d(-1, B)
				}
			} else d(-1, "No Transport");
			return y
		},
		param: function(a, c) {
			var f, d = [],
				e = function(a, b) {
					b = J.isFunction(b) ? b() : b, d[d.length] = encodeURIComponent(a) + "=" + encodeURIComponent(b)
				};
			if (c === b && (c = J.ajaxSettings.traditional), J.isArray(a) || a.jquery && !J.isPlainObject(a)) J.each(a, function() {
				e(this.name, this.value)
			});
			else for (f in a) l(f, a[f], c, e);
			return d.join("&").replace(Pb, "+")
		}
	}), J.extend({
		active: 0,
		lastModified: {},
		etag: {}
	}), jc = J.now(), kc = /(\=)\?(&|$)|\?\?/i, J.ajaxSetup({
		jsonp: "callback",
		jsonpCallback: function() {
			return J.expando + "_" + jc++
		}
	}), J.ajaxPrefilter("json jsonp", function(b, c, d) {
		var f, g, h, i, j, k, e = "string" == typeof b.data && /^application\/x\-www\-form\-urlencoded/.test(b.contentType);
		return "jsonp" === b.dataTypes[0] || b.jsonp !== !1 && (kc.test(b.url) || e && kc.test(b.data)) ? (g = b.jsonpCallback = J.isFunction(b.jsonpCallback) ? b.jsonpCallback() : b.jsonpCallback, h = a[g], i = b.url, j = b.data, k = "$1" + g + "$2", b.jsonp !== !1 && (i = i.replace(kc, k), b.url === i && (e && (j = j.replace(kc, k)), b.data === j && (i += (/\?/.test(i) ? "&" : "?") + b.jsonp + "=" + g))), b.url = i, b.data = j, a[g] = function(a) {
			f = [a]
		}, d.always(function() {
			a[g] = h, f && J.isFunction(h) && a[g](f[0])
		}), b.converters["script json"] = function() {
			return f || J.error(g + " was not called"), f[0]
		}, b.dataTypes[0] = "json", "script") : void 0
	}), J.ajaxSetup({
		accepts: {
			script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
		},
		contents: {
			script: /javascript|ecmascript/
		},
		converters: {
			"text script": function(a) {
				return J.globalEval(a), a
			}
		}
	}), J.ajaxPrefilter("script", function(a) {
		a.cache === b && (a.cache = !1), a.crossDomain && (a.type = "GET", a.global = !1)
	}), J.ajaxTransport("script", function(a) {
		if (a.crossDomain) {
			var c, d = G.head || G.getElementsByTagName("head")[0] || G.documentElement;
			return {
				send: function(e, f) {
					c = G.createElement("script"), c.async = "async", a.scriptCharset && (c.charset = a.scriptCharset), c.src = a.url, c.onload = c.onreadystatechange = function(a, e) {
						(e || !c.readyState || /loaded|complete/.test(c.readyState)) && (c.onload = c.onreadystatechange = null, d && c.parentNode && d.removeChild(c), c = b, e || f(200, "success"))
					}, d.insertBefore(c, d.firstChild)
				},
				abort: function() {
					c && c.onload(0, 1)
				}
			}
		}
	}), lc = a.ActiveXObject ?
	function() {
		for (var a in nc) nc[a](0, 1)
	} : !1, mc = 0, J.ajaxSettings.xhr = a.ActiveXObject ?
	function() {
		return !this.isLocal && i() || h()
	} : i, function(a) {
		J.extend(J.support, {
			ajax: !! a,
			cors: !! a && "withCredentials" in a
		})
	}(J.ajaxSettings.xhr()), J.support.ajax && J.ajaxTransport(function(c) {
		if (!c.crossDomain || J.support.cors) {
			var d;
			return {
				send: function(e, f) {
					var h, i, g = c.xhr();
					if (c.username ? g.open(c.type, c.url, c.async, c.username, c.password) : g.open(c.type, c.url, c.async), c.xhrFields) for (i in c.xhrFields) g[i] = c.xhrFields[i];
					c.mimeType && g.overrideMimeType && g.overrideMimeType(c.mimeType), !c.crossDomain && !e["X-Requested-With"] && (e["X-Requested-With"] = "XMLHttpRequest");
					try {
						for (i in e) g.setRequestHeader(i, e[i])
					} catch (j) {}
					g.send(c.hasContent && c.data || null), d = function(a, e) {
						var i, j, k, l, m;
						try {
							if (d && (e || 4 === g.readyState)) if (d = b, h && (g.onreadystatechange = J.noop, lc && delete nc[h]), e) 4 !== g.readyState && g.abort();
							else {
								i = g.status, k = g.getAllResponseHeaders(), l = {}, m = g.responseXML, m && m.documentElement && (l.xml = m);
								try {
									l.text = g.responseText
								} catch (a) {}
								try {
									j = g.statusText
								} catch (n) {
									j = ""
								}
								i || !c.isLocal || c.crossDomain ? 1223 === i && (i = 204) : i = l.text ? 200 : 404
							}
						} catch (o) {
							e || f(-1, o)
						}
						l && f(i, j, l, k)
					}, c.async && 4 !== g.readyState ? (h = ++mc, lc && (nc || (nc = {}, J(a).unload(lc)), nc[h] = d), g.onreadystatechange = d) : d()
				},
				abort: function() {
					d && d(0, 1)
				}
			}
		}
	}), oc = {}, rc = /^(?:toggle|show|hide)$/, sc = /^([+\-]=)?([\d+.\-]+)([a-z%]*)$/i, uc = [
		["height", "marginTop", "marginBottom", "paddingTop", "paddingBottom"],
		["width", "marginLeft", "marginRight", "paddingLeft", "paddingRight"],
		["opacity"]
	], J.fn.extend({
		show: function(a, b, c) {
			var f, g, h, i;
			if (a || 0 === a) return this.animate(e("show", 3), a, b, c);
			for (h = 0, i = this.length; i > h; h++) f = this[h], f.style && (g = f.style.display, !J._data(f, "olddisplay") && "none" === g && (g = f.style.display = ""), ("" === g && "none" === J.css(f, "display") || !J.contains(f.ownerDocument.documentElement, f)) && J._data(f, "olddisplay", d(f.nodeName)));
			for (h = 0; i > h; h++) f = this[h], f.style && (g = f.style.display, ("" === g || "none" === g) && (f.style.display = J._data(f, "olddisplay") || ""));
			return this
		},
		hide: function(a, b, c) {
			if (a || 0 === a) return this.animate(e("hide", 3), a, b, c);
			for (var d, f, g = 0, h = this.length; h > g; g++) d = this[g], d.style && (f = J.css(d, "display"), "none" !== f && !J._data(d, "olddisplay") && J._data(d, "olddisplay", f));
			for (g = 0; h > g; g++) this[g].style && (this[g].style.display = "none");
			return this
		},
		_toggle: J.fn.toggle,
		toggle: function(a, b, c) {
			var d = "boolean" == typeof a;
			return J.isFunction(a) && J.isFunction(b) ? this._toggle.apply(this, arguments) : null == a || d ? this.each(function() {
				var b = d ? a : J(this).is(":hidden");
				J(this)[b ? "show" : "hide"]()
			}) : this.animate(e("toggle", 3), a, b, c), this
		},
		fadeTo: function(a, b, c, d) {
			return this.filter(":hidden").css("opacity", 0).show().end().animate({
				opacity: b
			}, a, c, d)
		},
		animate: function(a, b, c, e) {
			function f() {
				g.queue === !1 && J._mark(this);
				var f, h, i, j, k, l, m, n, o, p, q, b = J.extend({}, g),
					c = 1 === this.nodeType,
					e = c && J(this).is(":hidden");
				b.animatedProperties = {};
				for (i in a) if (f = J.camelCase(i), i !== f && (a[f] = a[i], delete a[i]), (k = J.cssHooks[f]) && "expand" in k) {
					l = k.expand(a[f]), delete a[f];
					for (i in l) i in a || (a[i] = l[i])
				}
				for (f in a) {
					if (h = a[f], J.isArray(h) ? (b.animatedProperties[f] = h[1], h = a[f] = h[0]) : b.animatedProperties[f] = b.specialEasing && b.specialEasing[f] || b.easing || "swing", "hide" === h && e || "show" === h && !e) return b.complete.call(this);
					c && ("height" === f || "width" === f) && (b.overflow = [this.style.overflow, this.style.overflowX, this.style.overflowY], "inline" === J.css(this, "display") && "none" === J.css(this, "float") && (J.support.inlineBlockNeedsLayout && "inline" !== d(this.nodeName) ? this.style.zoom = 1 : this.style.display = "inline-block"))
				}
				null != b.overflow && (this.style.overflow = "hidden");
				for (i in a) j = new J.fx(this, b, i), h = a[i], rc.test(h) ? (q = J._data(this, "toggle" + i) || ("toggle" === h ? e ? "show" : "hide" : 0), q ? (J._data(this, "toggle" + i, "show" === q ? "hide" : "show"), j[q]()) : j[h]()) : (m = sc.exec(h), n = j.cur(), m ? (o = parseFloat(m[2]), p = m[3] || (J.cssNumber[i] ? "" : "px"), "px" !== p && (J.style(this, i, (o || 1) + p), n = (o || 1) / j.cur() * n, J.style(this, i, n + p)), m[1] && (o = ("-=" === m[1] ? -1 : 1) * o + n), j.custom(n, o, p)) : j.custom(n, h, ""));
				return !0
			}
			var g = J.speed(b, c, e);
			return J.isEmptyObject(a) ? this.each(g.complete, [!1]) : (a = J.extend({}, a), g.queue === !1 ? this.each(f) : this.queue(g.queue, f))
		},
		stop: function(a, c, d) {
			return "string" != typeof a && (d = c, c = a, a = b), c && a !== !1 && this.queue(a || "fx", []), this.each(function() {
				function b(a, b, c) {
					var e = b[c];
					J.removeData(a, c, !0), e.stop(d)
				}
				var c, e = !1,
					f = J.timers,
					g = J._data(this);
				if (d || J._unmark(!0, this), null == a) for (c in g) g[c] && g[c].stop && c.indexOf(".run") === c.length - 4 && b(this, g, c);
				else g[c = a + ".run"] && g[c].stop && b(this, g, c);
				for (c = f.length; c--;) f[c].elem === this && (null == a || f[c].queue === a) && (d ? f[c](!0) : f[c].saveState(), e = !0, f.splice(c, 1));
				(!d || !e) && J.dequeue(this, a)
			})
		}
	}), J.each({
		slideDown: e("show", 1),
		slideUp: e("hide", 1),
		slideToggle: e("toggle", 1),
		fadeIn: {
			opacity: "show"
		},
		fadeOut: {
			opacity: "hide"
		},
		fadeToggle: {
			opacity: "toggle"
		}
	}, function(a, b) {
		J.fn[a] = function(a, c, d) {
			return this.animate(b, a, c, d)
		}
	}), J.extend({
		speed: function(a, b, c) {
			var d = a && "object" == typeof a ? J.extend({}, a) : {
				complete: c || !c && b || J.isFunction(a) && a,
				duration: a,
				easing: c && b || b && !J.isFunction(b) && b
			};
			return d.duration = J.fx.off ? 0 : "number" == typeof d.duration ? d.duration : d.duration in J.fx.speeds ? J.fx.speeds[d.duration] : J.fx.speeds._default, (null == d.queue || d.queue === !0) && (d.queue = "fx"), d.old = d.complete, d.complete = function(a) {
				J.isFunction(d.old) && d.old.call(this), d.queue ? J.dequeue(this, d.queue) : a !== !1 && J._unmark(this)
			}, d
		},
		easing: {
			linear: function(a) {
				return a
			},
			swing: function(a) {
				return -Math.cos(a * Math.PI) / 2 + .5
			}
		},
		timers: [],
		fx: function(a, b, c) {
			this.options = b, this.elem = a, this.prop = c, b.orig = b.orig || {}
		}
	}), J.fx.prototype = {
		update: function() {
			this.options.step && this.options.step.call(this.elem, this.now, this), (J.fx.step[this.prop] || J.fx.step._default)(this)
		},
		cur: function() {
			if (null != this.elem[this.prop] && (!this.elem.style || null == this.elem.style[this.prop])) return this.elem[this.prop];
			var a, b = J.css(this.elem, this.prop);
			return isNaN(a = parseFloat(b)) ? b && "auto" !== b ? b : 0 : a
		},
		custom: function(a, c, d) {
			function e(a) {
				return f.step(a)
			}
			var f = this,
				h = J.fx;
			this.startTime = vc || g(), this.end = c, this.now = this.start = a, this.pos = this.state = 0, this.unit = d || this.unit || (J.cssNumber[this.prop] ? "" : "px"), e.queue = this.options.queue, e.elem = this.elem, e.saveState = function() {
				J._data(f.elem, "fxshow" + f.prop) === b && (f.options.hide ? J._data(f.elem, "fxshow" + f.prop, f.start) : f.options.show && J._data(f.elem, "fxshow" + f.prop, f.end))
			}, e() && J.timers.push(e) && !tc && (tc = setInterval(h.tick, h.interval))
		},
		show: function() {
			var a = J._data(this.elem, "fxshow" + this.prop);
			this.options.orig[this.prop] = a || J.style(this.elem, this.prop), this.options.show = !0, a !== b ? this.custom(this.cur(), a) : this.custom("width" === this.prop || "height" === this.prop ? 1 : 0, this.cur()), J(this.elem).show()
		},
		hide: function() {
			this.options.orig[this.prop] = J._data(this.elem, "fxshow" + this.prop) || J.style(this.elem, this.prop), this.options.hide = !0, this.custom(this.cur(), 0)
		},
		step: function(a) {
			var b, c, d, e = vc || g(),
				f = !0,
				h = this.elem,
				i = this.options;
			if (a || e >= i.duration + this.startTime) {
				this.now = this.end, this.pos = this.state = 1, this.update(), i.animatedProperties[this.prop] = !0;
				for (b in i.animatedProperties) i.animatedProperties[b] !== !0 && (f = !1);
				if (f) {
					if (null != i.overflow && !J.support.shrinkWrapBlocks && J.each(["", "X", "Y"], function(a, b) {
						h.style["overflow" + b] = i.overflow[a]
					}), i.hide && J(h).hide(), i.hide || i.show) for (b in i.animatedProperties) J.style(h, b, i.orig[b]), J.removeData(h, "fxshow" + b, !0), J.removeData(h, "toggle" + b, !0);
					d = i.complete, d && (i.complete = !1, d.call(h))
				}
				return !1
			}
			return 1 / 0 == i.duration ? this.now = e : (c = e - this.startTime, this.state = c / i.duration, this.pos = J.easing[i.animatedProperties[this.prop]](this.state, c, 0, 1, i.duration), this.now = this.start + (this.end - this.start) * this.pos), this.update(), !0
		}
	}, J.extend(J.fx, {
		tick: function() {
			for (var a, b = J.timers, c = 0; c < b.length; c++) a = b[c], !a() && b[c] === a && b.splice(c--, 1);
			b.length || J.fx.stop()
		},
		interval: 13,
		stop: function() {
			clearInterval(tc), tc = null
		},
		speeds: {
			slow: 600,
			fast: 200,
			_default: 400
		},
		step: {
			opacity: function(a) {
				J.style(a.elem, "opacity", a.now)
			},
			_default: function(a) {
				a.elem.style && null != a.elem.style[a.prop] ? a.elem.style[a.prop] = a.now + a.unit : a.elem[a.prop] = a.now
			}
		}
	}), J.each(uc.concat.apply([], uc), function(a, b) {
		b.indexOf("margin") && (J.fx.step[b] = function(a) {
			J.style(a.elem, b, Math.max(0, a.now) + a.unit)
		})
	}), J.expr && J.expr.filters && (J.expr.filters.animated = function(a) {
		return J.grep(J.timers, function(b) {
			return a === b.elem
		}).length
	}), xc = /^t(?:able|d|h)$/i, yc = /^(?:body|html)$/i, wc = "getBoundingClientRect" in G.documentElement ?
	function(a, b, d, e) {
		try {
			e = a.getBoundingClientRect()
		} catch (f) {}
		if (!e || !J.contains(d, a)) return e ? {
			top: e.top,
			left: e.left
		} : {
			top: 0,
			left: 0
		};
		var g = b.body,
			h = c(b),
			i = d.clientTop || g.clientTop || 0,
			j = d.clientLeft || g.clientLeft || 0,
			k = h.pageYOffset || J.support.boxModel && d.scrollTop || g.scrollTop,
			l = h.pageXOffset || J.support.boxModel && d.scrollLeft || g.scrollLeft,
			m = e.top + k - i,
			n = e.left + l - j;
		return {
			top: m,
			left: n
		}
	} : function(a, b, c) {
		for (var d, e = a.offsetParent, f = a, g = b.body, h = b.defaultView, i = h ? h.getComputedStyle(a, null) : a.currentStyle, j = a.offsetTop, k = a.offsetLeft;
		(a = a.parentNode) && a !== g && a !== c && (!J.support.fixedPosition || "fixed" !== i.position);) d = h ? h.getComputedStyle(a, null) : a.currentStyle, j -= a.scrollTop, k -= a.scrollLeft, a === e && (j += a.offsetTop, k += a.offsetLeft, J.support.doesNotAddBorder && (!J.support.doesAddBorderForTableAndCells || !xc.test(a.nodeName)) && (j += parseFloat(d.borderTopWidth) || 0, k += parseFloat(d.borderLeftWidth) || 0), f = e, e = a.offsetParent), J.support.subtractsBorderForOverflowNotVisible && "visible" !== d.overflow && (j += parseFloat(d.borderTopWidth) || 0, k += parseFloat(d.borderLeftWidth) || 0), i = d;
		return ("relative" === i.position || "static" === i.position) && (j += g.offsetTop, k += g.offsetLeft), J.support.fixedPosition && "fixed" === i.position && (j += Math.max(c.scrollTop, g.scrollTop), k += Math.max(c.scrollLeft, g.scrollLeft)), {
			top: j,
			left: k
		}
	}, J.fn.offset = function(a) {
		if (arguments.length) return a === b ? this : this.each(function(b) {
			J.offset.setOffset(this, a, b)
		});
		var c = this[0],
			d = c && c.ownerDocument;
		return d ? c === d.body ? J.offset.bodyOffset(c) : wc(c, d, d.documentElement) : null
	}, J.offset = {
		bodyOffset: function(a) {
			var b = a.offsetTop,
				c = a.offsetLeft;
			return J.support.doesNotIncludeMarginInBodyOffset && (b += parseFloat(J.css(a, "marginTop")) || 0, c += parseFloat(J.css(a, "marginLeft")) || 0), {
				top: b,
				left: c
			}
		},
		setOffset: function(a, b, c) {
			var l, m, e, f, g, h, i, j, k, d = J.css(a, "position");
			"static" === d && (a.style.position = "relative"), e = J(a), f = e.offset(), g = J.css(a, "top"), h = J.css(a, "left"), i = ("absolute" === d || "fixed" === d) && J.inArray("auto", [g, h]) > -1, j = {}, k = {}, i ? (k = e.position(), l = k.top, m = k.left) : (l = parseFloat(g) || 0, m = parseFloat(h) || 0), J.isFunction(b) && (b = b.call(a, c, f)), null != b.top && (j.top = b.top - f.top + l), null != b.left && (j.left = b.left - f.left + m), "using" in b ? b.using.call(a, j) : e.css(j)
		}
	}, J.fn.extend({
		position: function() {
			if (!this[0]) return null;
			var a = this[0],
				b = this.offsetParent(),
				c = this.offset(),
				d = yc.test(b[0].nodeName) ? {
					top: 0,
					left: 0
				} : b.offset();
			return c.top -= parseFloat(J.css(a, "marginTop")) || 0, c.left -= parseFloat(J.css(a, "marginLeft")) || 0, d.top += parseFloat(J.css(b[0], "borderTopWidth")) || 0, d.left += parseFloat(J.css(b[0], "borderLeftWidth")) || 0, {
				top: c.top - d.top,
				left: c.left - d.left
			}
		},
		offsetParent: function() {
			return this.map(function() {
				for (var a = this.offsetParent || G.body; a && !yc.test(a.nodeName) && "static" === J.css(a, "position");) a = a.offsetParent;
				return a
			})
		}
	}), J.each({
		scrollLeft: "pageXOffset",
		scrollTop: "pageYOffset"
	}, function(a, d) {
		var e = /Y/.test(d);
		J.fn[a] = function(f) {
			return J.access(this, function(a, f, g) {
				var h = c(a);
				return g === b ? h ? d in h ? h[d] : J.support.boxModel && h.document.documentElement[f] || h.document.body[f] : a[f] : (h ? h.scrollTo(e ? J(h).scrollLeft() : g, e ? g : J(h).scrollTop()) : a[f] = g, void 0)
			}, a, f, arguments.length, null)
		}
	}), J.each({
		Height: "height",
		Width: "width"
	}, function(a, c) {
		var d = "client" + a,
			e = "scroll" + a,
			f = "offset" + a;
		J.fn["inner" + a] = function() {
			var a = this[0];
			return a ? a.style ? parseFloat(J.css(a, c, "padding")) : this[c]() : null
		}, J.fn["outer" + a] = function(a) {
			var b = this[0];
			return b ? b.style ? parseFloat(J.css(b, c, a ? "margin" : "border")) : this[c]() : null
		}, J.fn[c] = function(a) {
			return J.access(this, function(a, c, g) {
				var h, i, j, k;
				return J.isWindow(a) ? (h = a.document, i = h.documentElement[d], J.support.boxModel && i || h.body && h.body[d] || i) : 9 === a.nodeType ? (h = a.documentElement, h[d] >= h[e] ? h[d] : Math.max(a.body[e], h[e], a.body[f], h[f])) : g === b ? (j = J.css(a, c), k = parseFloat(j), J.isNumeric(k) ? k : j) : (J(a).css(c, g), void 0)
			}, c, a, arguments.length, null)
		}
	}), a.jQuery = a.$ = J, "function" == typeof define && define.amd && define.amd.jQuery && define("jquery", [], function() {
		return J
	})
}(window), islogin = 0, syndomain = "", checkcookie(), PlayHistoryObj = new PlayHistoryClass, PlayHistoryObj.getPlayArray(), topShow = !1, islogin = 0, checkcookie(), $(function(a) {
	a.fn.changeList = function(b) {
		var c = {
			tag: "li",
			subName: ".utilTabSub",
			eventType: "click",
			num: 4,
			showType: "show"
		},
			d = a.extend({}, c, b),
			e = a(this),
			f = e.find(d.subName),
			g = f.find("li"),
			h = g.length,
			l = (g.outerWidth(!0), d.num),
			m = 0;
		h > l && e.find(d.tag)[d.eventType](function() {
			m = mathRand(l, h), g.hide(), a.each(m, function(a, b) {
				g.eq(b).fadeIn(800)
			})
		})
	}
}(jQuery)), $(function() {
	$(".drop-down").hover(function() {
		$(this).find(".drop-title").addClass("drop-title-hover"), $(this).find(".drop-box").show()
	}, function() {
		$(this).find(".drop-title").removeClass("drop-title-hover"), $(this).find(".drop-box").hide()
	})
}), $(document).ready(function() {
	$(".ui-input").focus(function() {
		$(this).addClass("ui-input-focus")
	}).hover(function() {
		$(this).addClass("ui-input-hover")
	}, function() {
		$(this).removeClass("ui-input-hover")
	}), $(".ui-input").blur(function() {
		$(this).removeClass("ui-input-focus")
	}), $(".ui-form-placeholder").each(function() {
		var a = $(this).find(".ui-label"),
			b = $(this).find(".ui-input"),
			c = $(this).find(".ui-input").val();
		"" != c && a.hide(), a.css("z-index", "3"), a.click(function() {
			$(this).hide(), b.focus()
		}), b.focus(function() {
			a.hide()
		})
	}), $(".ui-button").hover(function() {
		$(this).addClass("ui-button-hover")
	}, function() {
		$(this).removeClass("ui-button-hover")
	}), $(".close-his").click(function() {
		$(this).parents(".drop-box").hide()
	}), $(".show-title a").hover(function() {
		$(this).parent().parent().find(".tipInfo").show()
	}, function() {
		$(this).parent().parent().find(".tipInfo").hide()
	}), $("#wish").trigger("click"), $(".timeinfo").hover(function() {
		$(this).addClass("timeinfo-active")
	}, function() {
		$(this).removeClass("timeinfo-active")
	}), $(".date-list").each(function() {
		$lis = $(this).find("li:last").index(), $lis > 5 && $(this).addClass("date-long")
	}), $(".module-mov-new").changeList({
		tag: ".mov-time",
		subName: ".mov-time-ul",
		num: 2
	}), $(".new-mov-addtime").changeList({
		tag: ".module-mov-time",
		subName: ".module-mov-hot",
		num: 6
	}), $(".new-tv-addtime").changeList({
		tag: ".module-tv-time",
		subName: ".module-tv-hot",
		num: 6
	}), $("#star-new").changeList({
		tag: ".module-star-time",
		subName: ".index-star",
		num: 6
	}), $(".star-rlist").changeList({
		tag: ".list-star-time",
		subName: ".star-right",
		num: 9
	}), $(".index-jiaose").changeList({
		tag: ".module-jiaose-time",
		subName: ".index-jiaose-ul",
		num: 1
	})
}), $(document).ready(function() {
	$(".play_list a").each(function(a) {
		$(this).click(function() {
			var b, c;
			$(this).parent().hasClass("current") || (b = $(this).attr("title").split("-"), $(".detail-pic .text").text(b[1]), c = $(this).attr("id") + "-list", "bdhd-pl-list" != c && "qvod-pl-list" != c && "qiyi-pl-list" != c && "letv-pl-list" != c && $("#" + c + " .txt").text("( 无需安装任何插件，即可快速播放 )"), $(this).parent().nextAll().removeClass("current"), $(this).parent().prevAll().removeClass("current"), $(this).parent().addClass("current"), $(".vodplaybox").hide().css("opacity", 0), $(".vodplaybox:eq(" + a + ")").show().animate({
				opacity: "1"
			}, 1200))
		})
	}), $("#detail-list .order a").click(function() {
		var a, b;
		$(this).hasClass("asc") ? $(this).removeClass("asc").addClass("desc").text("降序") : $(this).removeClass("desc").addClass("asc").text("升序"), a = $(".vodplaybox:eq(" + $(this).attr("data") + ") .player_list"), b = $(".vodplaybox:eq(" + $(this).attr("data") + ") .player_list a"), a.html(b.get().reverse())
	})
}), $(document).ready(function() {
	$(".loading").lazyload({
		effect: "fadeIn"
	})
}), function(a, b, c, d) {
	var e = a(b);
	a.fn.lazyload = function(c) {
		function i() {
			var b = 0;
			f.each(function() {
				var c = a(this);
				if (!h.skip_invisible || c.is(":visible")) if (a.abovethetop(this, h) || a.leftofbegin(this, h));
				else if (a.belowthefold(this, h) || a.rightoffold(this, h)) {
					if (++b > h.failure_limit) return !1
				} else c.trigger("appear"), b = 0
			})
		}
		var g, f = this,
			h = {
				threshold: 0,
				failure_limit: 0,
				event: "scroll",
				effect: "show",
				container: b,
				data_attribute: "original",
				skip_invisible: !0,
				appear: null,
				load: null,
				errorimgpic: ""
			};
		return c && (d !== c.failurelimit && (c.failure_limit = c.failurelimit, delete c.failurelimit), d !== c.effectspeed && (c.effect_speed = c.effectspeed, delete c.effectspeed), a.extend(h, c)), g = h.container === d || h.container === b ? e : a(h.container), 0 === h.event.indexOf("scroll") && g.bind(h.event, function() {
			return i()
		}), this.each(function() {
			var b = this,
				c = a(b);
			b.loaded = !1, c.one("appear", function() {
				if (!this.loaded) {
					if (h.appear) {
						var d = f.length;
						h.appear.call(b, d, h)
					}
					a("<img />").bind("load", function() {
						var d, e;
						c.hide().attr("src", c.data(h.data_attribute))[h.effect](h.effect_speed), b.loaded = !0, d = a.grep(f, function(a) {
							return !a.loaded
						}), f = a(d), h.load && (e = f.length, h.load.call(b, e, h))
					}).bind("error", function() {
						c.attr("src", h.errorimgpic)
					}).attr("src", c.data(h.data_attribute))
				}
			}), 0 !== h.event.indexOf("scroll") && c.bind(h.event, function() {
				b.loaded || c.trigger("appear")
			})
		}), e.bind("resize", function() {
			i()
		}), /iphone|ipod|ipad.*os 5/gi.test(navigator.appVersion) && e.bind("pageshow", function(b) {
			b.originalEvent.persisted && f.each(function() {
				a(this).trigger("appear")
			})
		}), a(b).load(function() {
			i()
		}), this
	}, a.belowthefold = function(c, f) {
		var g;
		return g = f.container === d || f.container === b ? e.height() + e.scrollTop() : a(f.container).offset().top + a(f.container).height(), g <= a(c).offset().top - f.threshold
	}, a.rightoffold = function(c, f) {
		var g;
		return g = f.container === d || f.container === b ? e.width() + e.scrollLeft() : a(f.container).offset().left + a(f.container).width(), g <= a(c).offset().left - f.threshold
	}, a.abovethetop = function(c, f) {
		var g;
		return g = f.container === d || f.container === b ? e.scrollTop() : a(f.container).offset().top, g >= a(c).offset().top + f.threshold + a(c).height()
	}, a.leftofbegin = function(c, f) {
		var g;
		return g = f.container === d || f.container === b ? e.scrollLeft() : a(f.container).offset().left, g >= a(c).offset().left + f.threshold + a(c).width()
	}, a.inviewport = function(b, c) {
		return !(a.rightoffold(b, c) || a.leftofbegin(b, c) || a.belowthefold(b, c) || a.abovethetop(b, c))
	}, a.extend(a.expr[":"], {
		"below-the-fold": function(b) {
			return a.belowthefold(b, {
				threshold: 0
			})
		},
		"above-the-top": function(b) {
			return !a.belowthefold(b, {
				threshold: 0
			})
		},
		"right-of-screen": function(b) {
			return a.rightoffold(b, {
				threshold: 0
			})
		},
		"left-of-screen": function(b) {
			return !a.rightoffold(b, {
				threshold: 0
			})
		},
		"in-viewport": function(b) {
			return a.inviewport(b, {
				threshold: 0
			})
		},
		"above-the-fold": function(b) {
			return !a.belowthefold(b, {
				threshold: 0
			})
		},
		"right-of-fold": function(b) {
			return a.rightoffold(b, {
				threshold: 0
			})
		},
		"left-of-fold": function(b) {
			return !a.rightoffold(b, {
				threshold: 0
			})
		}
	})
}(jQuery, window, document), function(a) {
	a.fn.extend({
		autocomplete: function(b, c) {
			var d = "string" == typeof b;
			return c = a.extend({}, a.Autocompleter.defaults, {
				url: d ? b : null,
				data: d ? null : b,
				delay: d ? a.Autocompleter.defaults.delay : 10,
				max: c && !c.scroll ? 10 : 150
			}, c), c.highlight = c.highlight ||
			function(a) {
				return a
			}, c.formatMatch = c.formatMatch || c.formatItem, this.each(function() {
				new a.Autocompleter(this, c)
			})
		},
		result: function(a) {
			return this.bind("result", a)
		},
		search: function(a) {
			return this.trigger("search", [a])
		},
		flushCache: function() {
			return this.trigger("flushCache")
		},
		setOptions: function(a) {
			return this.trigger("setOptions", [a])
		},
		unautocomplete: function() {
			return this.trigger("unautocomplete")
		}
	}), a.Autocompleter = function(b, c) {
		function n() {
			var f, h, i, j, k, m, d = l.selected();
			return d ? (f = d.result, g = f, c.multiple && (h = p(e.val()), h.length > 1 && (i = c.multipleSeparator.length, j = a(b).selection().start, m = 0, a.each(h, function(a, b) {
				return m += b.length, m >= j ? (k = a, !1) : (m += i, void 0)
			}), h[k] = f, f = h.join(c.multipleSeparator)), f += c.multipleSeparator), e.val(f), t(), e.trigger("result", [d.data, d.value]), !0) : !1
		}
		function o(a, b) {
			if (j == d.DEL) return l.hide(), void 0;
			var f = e.val();
			(b || f != g) && (g = f, f = q(f), f.length >= c.minChars ? (e.addClass(c.loadingClass), c.matchCase || (f = f.toLowerCase()), v(f, u, t)) : (x(), l.hide()))
		}
		function p(b) {
			return b ? c.multiple ? a.map(b.split(c.multipleSeparator), function(c) {
				return a.trim(b).length ? a.trim(c) : null
			}) : [a.trim(b)] : [""]
		}
		function q(d) {
			var e, f;
			return c.multiple ? (e = p(d), 1 == e.length ? e[0] : (f = a(b).selection().start, e = f == d.length ? p(d) : p(d.replace(d.substring(f), "")), e[e.length - 1])) : d
		}
		function r(f, h) {
			c.autoFill && q(e.val()).toLowerCase() == f.toLowerCase() && j != d.BACKSPACE && (e.val(e.val() + h.substring(q(g).length)), a(b).selection(g.length, g.length + h.length))
		}
		function s() {
			clearTimeout(f), f = setTimeout(t, 200)
		}
		function t() {
			l.visible(), l.hide(), clearTimeout(f), x(), c.mustMatch && e.search(function(a) {
				if (!a) if (c.multiple) {
					var b = p(e.val()).slice(0, -1);
					e.val(b.join(c.multipleSeparator) + (b.length ? c.multipleSeparator : ""))
				} else e.val(""), e.trigger("result", null)
			})
		}
		function u(a, b) {
			b && b.length && i ? (x(), l.display(b, a), r(a, b[0].value), l.show()) : t()
		}
		function v(d, e, f) {
			var g, i;
			c.matchCase || (d = d.toLowerCase()), g = h.load(d), g && g.length ? e(d, g) : "string" == typeof c.url && c.url.length > 0 ? (i = {
				timestamp: +new Date
			}, a.each(c.extraParams, function(a, b) {
				i[a] = "function" == typeof b ? b() : b
			}), a.ajax({
				mode: "abort",
				port: "autocomplete" + b.name,
				dataType: c.dataType,
				url: c.url,
				data: a.extend({
					q: q(d),
					limit: c.max
				}, i),
				success: function(a) {
					var b = c.parse && c.parse(a) || w(a);
					h.add(d, b), e(d, b)
				}
			})) : (l.emptyList(), f(d))
		}
		function w(b) {
			var f, g, d = [],
				e = b.split("\n");
			for (f = 0; f < e.length; f++) g = a.trim(e[f]), g && (g = g.split("|"), d[d.length] = {
				data: g,
				value: g[0],
				result: c.formatResult && c.formatResult(g, g[0]) || g[0]
			});
			return d
		}
		function x() {
			e.removeClass(c.loadingClass)
		}
		var f, j, m, d = {
			UP: 38,
			DOWN: 40,
			DEL: 46,
			TAB: 9,
			RETURN: 13,
			ESC: 27,
			COMMA: 188,
			PAGEUP: 33,
			PAGEDOWN: 34,
			BACKSPACE: 8
		},
			e = a(b).attr("autocomplete", "off").addClass(c.inputClass),
			g = "",
			h = a.Autocompleter.Cache(c),
			i = 0,
			k = {
				mouseDownOnSelect: !1
			},
			l = a.Autocompleter.Select(c, b, n, k);
		a.browser.opera && a(b.form).bind("submit.autocomplete", function() {
			return m ? (m = !1, !1) : void 0
		}), e.bind((a.browser.opera ? "keypress" : "keydown") + ".autocomplete", function(b) {
			switch (i = 1, j = b.keyCode, b.keyCode) {
			case d.UP:
				b.preventDefault(), l.visible() ? l.prev() : o(0, !0);
				break;
			case d.DOWN:
				b.preventDefault(), l.visible() ? l.next() : o(0, !0);
				break;
			case d.PAGEUP:
				b.preventDefault(), l.visible() ? l.pageUp() : o(0, !0);
				break;
			case d.PAGEDOWN:
				b.preventDefault(), l.visible() ? l.pageDown() : o(0, !0);
				break;
			case c.multiple && "," == a.trim(c.multipleSeparator) && d.COMMA:
			case d.TAB:
			case d.RETURN:
				if (n()) return b.preventDefault(), m = !0, !1;
				break;
			case d.ESC:
				l.hide();
				break;
			default:
				clearTimeout(f), f = setTimeout(o, c.delay)
			}
		}).focus(function() {
			i++
		}).blur(function() {
			i = 0, k.mouseDownOnSelect || s()
		}).click(function() {
			i++ > 1 && !l.visible() && o(0, !0)
		}).bind("search", function() {
			function c(a, c) {
				var d, f;
				if (c && c.length) for (f = 0; f < c.length; f++) if (c[f].result.toLowerCase() == a.toLowerCase()) {
					d = c[f];
					break
				}
				"function" == typeof b ? b(d) : e.trigger("result", d && [d.data, d.value])
			}
			var b = arguments.length > 1 ? arguments[1] : null;
			a.each(p(e.val()), function(a, b) {
				v(b, c, c)
			})
		}).bind("flushCache", function() {
			h.flush()
		}).bind("setOptions", function() {
			a.extend(c, arguments[1]), "data" in arguments[1] && h.populate()
		}).bind("unautocomplete", function() {
			l.unbind(), e.unbind(), a(b.form).unbind(".autocomplete")
		})
	}, a.Autocompleter.defaults = {
		inputClass: "acInput",
		resultsClass: "acResults",
		loadingClass: "acLoading",
		minChars: 1,
		delay: 200,
		matchCase: !1,
		matchSubset: !0,
		matchContains: !1,
		cacheLength: 10,
		max: 100,
		mustMatch: !1,
		extraParams: {},
		selectFirst: !0,
		formatItem: function(a) {
			return a[0]
		},
		formatMatch: null,
		autoFill: !1,
		width: 0,
		multiple: !1,
		multipleSeparator: ", ",
		highlight: function(a, b) {
			return a.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + b.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>")
		},
		scroll: !0,
		scrollHeight: 180
	}, a.Autocompleter.Cache = function(b) {
		function e(a, c) {
			b.matchCase || (a = a.toLowerCase());
			var d = a.indexOf(c);
			return "word" == b.matchContains && (d = a.toLowerCase().search("\\b" + c.toLowerCase())), -1 == d ? !1 : 0 == d || b.matchContains
		}
		function f(a, e) {
			d > b.cacheLength && h(), c[a] || d++, c[a] = e
		}
		function g() {
			var c, d, e, g, h, i, j, k;
			if (!b.data) return !1;
			for (c = {}, d = 0, b.url || (b.cacheLength = 1), c[""] = [], e = 0, g = b.data.length; g > e; e++) h = b.data[e], h = "string" == typeof h ? [h] : h, i = b.formatMatch(h, e + 1, b.data.length), i !== !1 && (j = i.charAt(0).toLowerCase(), c[j] || (c[j] = []), k = {
				value: i,
				data: h,
				result: b.formatResult && b.formatResult(h) || i
			}, c[j].push(k), d++ < b.max && c[""].push(k));
			a.each(c, function(a, c) {
				b.cacheLength++, f(a, c)
			})
		}
		function h() {
			c = {}, d = 0
		}
		var c = {},
			d = 0;
		return setTimeout(g, 25), {
			flush: h,
			add: f,
			populate: g,
			load: function(f) {
				var g, h, i, j;
				if (!b.cacheLength || !d) return null;
				if (!b.url && b.matchContains) {
					g = [];
					for (h in c) h.length > 0 && (i = c[h], a.each(i, function(a, b) {
						e(b.value, f) && g.push(b)
					}));
					return g
				}
				if (c[f]) return c[f];
				if (b.matchSubset) for (j = f.length - 1; j >= b.minChars; j--) if (i = c[f.substr(0, j)]) return g = [], a.each(i, function(a, b) {
					e(b.value, f) && (g[g.length] = b)
				}), g;
				return null
			}
		}
	}, a.Autocompleter.Select = function(b, c, d, e) {
		function n() {
			k && (l = a("<div/>").hide().addClass(b.resultsClass).css("position", "absolute").appendTo(document.body), m = a("<ul/>").appendTo(l).mouseover(function(b) {
				o(b).nodeName && "LI" == o(b).nodeName.toUpperCase() && (h = a("li", m).removeClass(f.ACTIVE).index(o(b)), a(o(b)).addClass(f.ACTIVE))
			}).click(function(b) {
				return a(o(b)).addClass(f.ACTIVE), d(), c.focus(), !1
			}).mousedown(function() {
				e.mouseDownOnSelect = !0
			}).mouseup(function() {
				e.mouseDownOnSelect = !1
			}), b.width > 0 && l.css("width", b.width), k = !1)
		}
		function o(a) {
			for (var b = a.target; b && "LI" != b.tagName;) b = b.parentNode;
			return b ? b : []
		}
		function p(a) {
			var c, d;
			g.slice(h, h + 1).removeClass(f.ACTIVE), q(a), c = g.slice(h, h + 1).addClass(f.ACTIVE), b.scroll && (d = 0, g.slice(0, h).each(function() {
				d += this.offsetHeight
			}), d + c[0].offsetHeight - m.scrollTop() > m[0].clientHeight ? m.scrollTop(d + c[0].offsetHeight - m.innerHeight()) : d < m.scrollTop() && m.scrollTop(d))
		}
		function q(a) {
			h += a, 0 > h ? h = g.size() - 1 : h >= g.size() && (h = 0)
		}
		function r(a) {
			return b.max && b.max < a ? b.max : a
		}
		function s() {
			var c, d, e, k;
			for (m.empty(), c = r(i.length), d = 0; c > d; d++) i[d] && (e = b.formatItem(i[d].data, d + 1, c, i[d].value, j), e !== !1 && (k = a("<li/>").html(b.highlight(e, j)).addClass(0 == d % 2 ? "ac_even" : "ac_odd").appendTo(m)[0], a.data(k, "ac_data", i[d])));
			g = m.find("li"), b.selectFirst && (g.slice(0, 1).addClass(f.ACTIVE), h = 0), a.fn.bgiframe && m.bgiframe()
		}
		var g, i, l, m, f = {
			ACTIVE: "acover"
		},
			h = -1,
			j = "",
			k = !0;
		return {
			display: function(a, b) {
				n(), i = a, j = b, s()
			},
			next: function() {
				p(1)
			},
			prev: function() {
				p(-1)
			},
			pageUp: function() {
				0 != h && 0 > h - 8 ? p(-h) : p(-8)
			},
			pageDown: function() {
				h != g.size() - 1 && h + 8 > g.size() ? p(g.size() - 1 - h) : p(8)
			},
			hide: function() {
				l && l.hide(), g && g.removeClass(f.ACTIVE), h = -1
			},
			visible: function() {
				return l && l.is(":visible")
			},
			current: function() {
				return this.visible() && (g.filter("." + f.ACTIVE)[0] || b.selectFirst && g[0])
			},
			show: function() {
				var e, f, d = a(c).offset();
				l.css({
					width: "string" == typeof b.width || b.width > 0 ? b.width : a(c).width(),
					top: d.top + c.offsetHeight,
					left: d.left
				}).show(), b.scroll && (m.scrollTop(0), m.css({
					maxHeight: b.scrollHeight,
					overflow: "auto"
				}), a.browser.msie && "undefined" == typeof document.body.style.maxHeight && (e = 0, g.each(function() {
					e += this.offsetHeight
				}), f = e > b.scrollHeight, m.css("height", f ? b.scrollHeight : e), f || g.width(m.width() - parseInt(g.css("padding-left")) - parseInt(g.css("padding-right")))))
			},
			selected: function() {
				var b = g && g.filter("." + f.ACTIVE).removeClass(f.ACTIVE);
				return b && b.length && a.data(b[0], "ac_data")
			},
			emptyList: function() {
				m && m.empty()
			},
			unbind: function() {
				l && l.remove()
			}
		}
	}, a.fn.selection = function(a, b) {
		var c, d, e, f, g, h;
		return void 0 !== a ? this.each(function() {
			if (this.createTextRange) {
				var c = this.createTextRange();
				void 0 === b || a == b ? (c.move("character", a), c.select()) : (c.collapse(!0), c.moveStart("character", a), c.moveEnd("character", b), c.select())
			} else this.setSelectionRange ? this.setSelectionRange(a, b) : this.selectionStart && (this.selectionStart = a, this.selectionEnd = b)
		}) : (c = this[0], c.createTextRange ? (d = document.selection.createRange(), e = c.value, f = "<->", g = d.text.length, d.text = f, h = c.value.indexOf(f), c.value = e, this.selection(h, h + g), {
			start: h,
			end: h + g
		}) : void 0 !== c.selectionStart ? {
			start: c.selectionStart,
			end: c.selectionEnd
		} : void 0)
	}
}(jQuery), FF = {
	Home: {
		Url: document.URL,
		Tpl: "defalut",
		Channel: "",
		GetChannel: function(a) {
			return "1" == a ? "vod" : "2" == a ? "news" : "3" == a ? "special" : void 0
		},
		Js: function() {
			this.Channel = this.GetChannel(Sid), $("#wd").length > 0 && ("2" == Sid ? ($key = "输入关键字", $("#ffsearch").attr("action", Root + "index.php@s=news-search")) : $key = "输入影片名称或主演名称", "" == $("#wd").val() && $("#wd").val($key), $("#wd").focus(function() {
				$("#wd").val() == $key && $("#wd").val("")
			}), $("#wd").blur(function() {
				"" == $("#wd").val() && $("#wd").val($key)
			})), $("#fav").click(function() {
				var a = window.location.href;
				try {
					window.external.addFavorite(a, document.title)
				} catch (b) {
					try {
						window.sidebar.addPanel(document.title, a, "")
					} catch (b) {
						alert("请使用Ctrl+D为您的浏览器添加书签！")
					}
				}
			}), $("img.lazy").error(function() {
				$(this).attr("src", Root + "Tpls/PPTV/nophoto.jpg")
			}), $("img.bigimg").error(function() {
				$(this).attr("src", Root + "Tpls/PPTV/nophoto2.jpg")
			}), $(".img_txt img,.pic_list_comm .pic_list img").hover(function() {
				$(this).css("border-color", "#333333")
			}, function() {
				$(this).css("border-color", "#dddddd")
			})
		}
	},
	UpDown: {
		Vod: function(a) {
			($("#Up").length || $("#Down").length) && this.Ajax(a, "vod", ""), $(".Up").click(function() {
				FF.UpDown.Ajax(a, "vod", "up")
			}), $(".Down").click(function() {
				FF.UpDown.Ajax(a, "vod", "down")
			})
		},
		News: function(a) {
			$("#Digup").length || $("#Digdown").length ? this.Ajax(a, "news", "") : FF.UpDown.Show($("#Digup_val").html() + ":" + $("#Digdown_val").html(), "news"), $(".Digup").click(function() {
				FF.UpDown.Ajax(a, "news", "up")
			}), $(".Digdown").click(function() {
				FF.UpDown.Ajax(a, "news", "down")
			})
		},
		Ajax: function(a, b, c) {
			$.ajax({
				type: "get",
				url: a + "-type-" + c,
				timeout: 5e3,
				dataType: "json",
				success: function(a) {
					a.status ? FF.UpDown.Show(a.data, b) : alert(a.info)
				}
			})
		},
		Show: function(a, b) {
			var c, d, e, f, g, h;
			"vod" == b ? ($(".Up>span").html(a.split(":")[0]), $(".Down>span").html(a.split(":")[1])) : (b = "news") && (c = a.split(":"), d = parseInt(c[0]), e = parseInt(c[1]), f = d + e, g = 100 * (d / f), g = Math.round(10 * g) / 10, h = 100 - g, h = Math.round(10 * h) / 10, 0 != f && ($("#Digup_val").html(d), $("#Digdown_val").html(e), $("#Digup_sp").html(g + "%"), $("#Digdown_sp").html(h + "%"), $("#Digup_img").width(parseInt(55 * (d / f))), $("#Digdown_img").width(parseInt(55 * (e / f)))))
		}
	},
	Lazyload: {
		Show: function() {
			$("img.loading").lazyload()
		}
	},
	Playlist: {
		Show: function() {
			var a = $("#playlistit a"),
				b = $("#playlist .playlist");
			a.mousemove(function() {
				var c = a.index($(this));
				return $(this).addClass("on").siblings().removeClass("on"), b.hide(), $(b.get(c)).show(), !1
			})
		}
	},
	Suggest: {
		Show: function(a, b, c) {
			$("#" + a).autocomplete(c, {
				width: 458,
				scrollHeight: 320,
				minChars: 1,
				matchSubset: 1,
				max: b,
				cacheLength: 10,
				multiple: !0,
				matchContains: !0,
				autoFill: !1,
				dataType: "json",
				parse: function(a) {
					var b, c;
					if (a.status) {
						for (b = [], c = 0; c < a.data.length; c++) b[c] = {
							data: a.data[c],
							value: a.data[c].vod_name,
							result: a.data[c].vod_name
						};
						return b
					}
					return {
						data: "",
						value: "",
						result: ""
					}
				},
				formatItem: function(a) {
					return a.vod_name
				},
				formatResult: function(a) {
					return a.vod_name
				}
			}).result(function(a, b) {
				location.href = b.vod_url, location.href = info
			})
		}
	},
	Cookie: {
		Set: function(a, b, c) {
			var d = new Date;
			d.setTime(d.getTime() + 1e3 * 60 * 60 * 24 * c), document.cookie.match(new RegExp("(^| )" + a + "=([^;]*)(;|$)")), document.cookie = a + "=" + escape(b) + ";path=/;expires=" + d.toUTCString()
		},
		Get: function(a) {
			var b = document.cookie.match(new RegExp("(^| )" + a + "=([^;]*)(;|$)"));
			return null != b ? unescape(b[2]) : void 0
		},
		Del: function(a) {
			var c, b = new Date;
			b.setTime(b.getTime() - 1), c = this.Get(a), null != c && (document.cookie = a + "=" + escape(c) + ";path=/;expires=" + b.toUTCString())
		}
	},
	History: {
		Json: "",
		Display: !0,
		List: function(a) {
			this.Create(a), $("#" + a).hover(function() {
				FF.History.Show()
			}, function() {
				FF.History.FlagHide()
			})
		},
		Create: function($id) {
			var jsonstr, jsondata = [];
			if (this.Json ? jsondata = this.Json : (jsonstr = FF.Cookie.Get("FF_Cookie"), void 0 != jsonstr && (jsondata = eval(jsonstr))), html = '<table class="history_list sr0" id="hishow" cellpadding="0" cellspacing="0">', html += '<thead><tr><th colspan="2"><b id="clearall"><a href="javascript:void(0)" onclick="FF.History.Clear();">清空</a></b></th></tr></thead>', jsondata.length > 0) {
				for (html += "<tbody>", $i = 0; $i < jsondata.length; $i++) html += '<tr class="r' + [$i] + ' hisitem"><td class="op"><b class="del"></b></td><td class="tt"><a href="' + jsondata[$i].vodlink + '" title="' + jsondata[$i].vodname + '">' + jsondata[$i].vodname + "</a></td></tr>";
				html += "</tbody>"
			} else html += '<tfoot><tr class="emptied"><th colspan="2">暂无浏览记录</th></tr></tfoot>';
			html += "</table>", $("#history_layer").html(html)
		},
		Insert: function(vodname, vodlink, limit, days, cidname, vodpic) {
			var jsondata = FF.Cookie.Get("FF_Cookie");
			if (void 0 != jsondata) {
				for (this.Json = eval(jsondata), $i = 0; $i < this.Json.length; $i++) if (this.Json[$i].vodlink == vodlink) return !1;
				for (vodlink || (vodlink = document.URL), jsonstr = '{video:[{"vodname":"' + vodname + '","vodlink":"' + vodlink + '","cidname":"' + cidname + '","vodpic":"' + vodpic + '"},', $i = 0; limit >= $i && this.Json[$i]; $i++) jsonstr += '{"vodname":"' + this.Json[$i].vodname + '","vodlink":"' + this.Json[$i].vodlink + '","cidname":"' + this.Json[$i].cidname + $i + '","vodpic":"' + this.Json[$i].vodpic + '"},';
				jsonstr = jsonstr.substring(0, jsonstr.lastIndexOf(",")), jsonstr += "]}"
			} else jsonstr = '{video:[{"vodname":"' + vodname + '","vodlink":"' + vodlink + '","cidname":"' + cidname + '","vodpic":"' + vodpic + '"}]}';
			this.Json = eval(jsonstr), FF.Cookie.Set("FF_Cookie", jsonstr, days)
		}
	},
	Comment: {
		Default: function(a) {
			$("#Comment").length > 0 && FF.Comment.Show(a)
		},
		Show: function(a) {
			$.ajax({
				type: "get",
				url: a,
				timeout: 5e3,
				error: function() {
					$("#Comment").html("评论加载失败...")
				},
				success: function(a) {
					$("#Comment").html(a)
				}
			})
		},
		Post: function() {
			if ("请在这里发表您的个人看法，最多200个字。" == $("#comment_content").val()) return $("#comment_tips").html("请发表您的评论观点！"), !1;
			var a = "cm_sid=" + Sid + "&cm_cid=" + Id + "&cm_content=" + $("#comment_content").val();
			$.ajax({
				type: "post",
				url: Root + "index.php?s=Cm-insert",
				data: a,
				dataType: "json",
				success: function(a) {
					1 == a.status && FF.Comment.Show(Root + "index.php?s=Cm-Show-sid-" + Sid + "-id-" + Id + "-p-1"), alert(a.info)
				}
			})
		}
	}
}, $(document).ready(function() {
	FF.Home.Js(), FF.Lazyload.Show(), FF.Playlist.Show(), FF.Suggest.Show("wd", 12, Root + "index.php?s=home-search-vod", Root + "index.php?s=vod-search-wd-"), FF.History.List("history"), FF.UpDown.Vod(Root + "index.php?s=Updown-" + FF.Home.Channel + "-id-" + Id), FF.UpDown.News(Root + "index.php?s=Updown-" + FF.Home.Channel + "-id-" + Id), FF.Comment.Default(Root + "index.php?s=Cm-Show-sid-" + Sid + "-id-" + Id + "-p-1")
}), $(document).ready(function() {
	$("#yybox").length > 0 && $("#yybox")[0].scrollHeight > 28 && ($("#yymoerbtn").show(), $("#yybox").width(305), $("#yymoerbtn").click(function(a) {
		var b, c;
		$("#yybox").height() > 28 ? (b = $("#movtxtbox")[0].scrollHeight - 10, "Microsoft Internet Explorer" == navigator.appName && "7." == navigator.appVersion.match(/7./i) ? b += 11 : "6.0" == $.browser.version && (b += 1), c = $("#yybox")[0].scrollHeight, $("#yybox").height(28), $("#movtxtbox").height(b - c + 28)) : (b = $("#movtxtbox")[0].scrollHeight - 10, c = $("#yybox")[0].scrollHeight, $("#yybox").height(c), $("#movtxtbox").height(b + (c - 28))), a.preventDefault()
	})), $("#yyboxs").length > 0 && $("#yyboxs")[0].scrollHeight > 35 && ($("#yymoerbtns").show(), $("#yyboxs").width(660), $("#yymoerbtns").click(function(a) {
		var b, c;
		$("#yyboxs").height() > 35 ? (b = $("#movtxtboxs")[0].scrollHeight - 10, "Microsoft Internet Explorer" == navigator.appName && "7." == navigator.appVersion.match(/7./i) ? b += 11 : "6.0" == $.browser.version && (b += 1), c = $("#yyboxs")[0].scrollHeight, $("#yyboxs").height(35), $("#movtxtboxs").height(b - c + 35)) : (b = $("#movtxtboxs")[0].scrollHeight - 10, c = $("#yyboxs")[0].scrollHeight, $("#yyboxs").height(c), $("#movtxtboxs").height(b + (c - 35))), a.preventDefault()
	})), $("#downul").length > 0 && $("#downul")[0].scrollHeight > 305 && ($("#downzk").show(), $("#downul").height(210), $("#downzk").click(function(a) {
		var b;
		$(this).hasClass("ss") ? $(this).removeClass("ss").addClass("zk").text("展开全部") : $(this).removeClass("zk").addClass("ss").text("收缩部分"), $("#downul").height() > 305 ? (b = $("#downul")[0].scrollHeight, $("#downul").height(210)) : (b = $("#downul")[0].scrollHeight, $("#downul").height(b)), a.preventDefault()
	})), $("#actorall").length > 0 && $("#actorall")[0].scrollHeight > 320 && ($("#downzk").show(), $("#actorall").height(320), $("#downzk").click(function(a) {
		var b;
		$(this).hasClass("ss") ? $(this).removeClass("ss").addClass("zk").text("查看全部") : $(this).removeClass("zk").addClass("ss").text("收缩部分"), $("#actorall").height() > 320 ? (b = $("#actorall")[0].scrollHeight, $("#actorall").height(320)) : (b = $("#actorall")[0].scrollHeight, $("#actorall").height(b)), a.preventDefault()
	}))
}), function(a) {
	a.fn.slide = function(b) {
		return a.fn.slide.defaults = {
			effect: "fade",
			autoPlay: !1,
			delayTime: 500,
			interTime: 2500,
			triggerTime: 150,
			defaultIndex: 0,
			titCell: ".hd li",
			mainCell: ".bd",
			targetCell: null,
			trigger: "mouseover",
			scroll: 1,
			vis: 1,
			titOnClassName: "on",
			autoPage: !1,
			prevCell: ".prev",
			nextCell: ".next",
			pageStateCell: ".pageState",
			opp: !1,
			pnLoop: !0,
			easing: "linear",
			startFun: null,
			endFun: null,
			switchLoad: null
		}, this.each(function() {
			var m, n, o, p, q, r, s, t, u, v, w, x, y, z, A, B, C, D, E, F, G, H, I, J, c = a.extend({}, a.fn.slide.defaults, b),
				d = c.effect,
				e = a(c.prevCell, a(this)),
				f = a(c.nextCell, a(this)),
				g = a(c.pageStateCell, a(this)),
				h = a(c.titCell, a(this)),
				i = h.size(),
				j = a(c.mainCell, a(this)),
				k = j.children().size(),
				l = c.switchLoad;
			if (null != c.targetCell && (m = a(c.targetCell, a(this))), n = parseInt(c.defaultIndex), o = parseInt(c.delayTime), p = parseInt(c.interTime), parseInt(c.triggerTime), q = parseInt(c.scroll), r = parseInt(c.vis), s = "false" == c.autoPlay || 0 == c.autoPlay ? !1 : !0, t = "false" == c.opp || 0 == c.opp ? !1 : !0, u = "false" == c.autoPage || 0 == c.autoPage ? !1 : !0, v = "false" == c.pnLoop || 0 == c.pnLoop ? !1 : !0, w = 0, x = 0, y = 0, z = 0, A = c.easing, B = null, C = n, 0 == i && (i = k), u) {
				for (D = k - r, i = 1 + parseInt(0 != D % q ? D / q + 1 : D / q), 0 >= i && (i = 1), h.html(""), E = 0; i > E; E++) h.append("<li>" + (E + 1) + "</li>");
				h = a("li", h)
			}
			if (j.children().each(function() {
				a(this).width() > y && (y = a(this).width(), x = a(this).outerWidth(!0)), a(this).height() > z && (z = a(this).height(), w = a(this).outerHeight(!0))
			}), k >= r) switch (d) {
			case "fold":
				j.css({
					position: "relative",
					width: x,
					height: w
				}).children().css({
					position: "absolute",
					width: y,
					left: 0,
					top: 0,
					display: "none"
				});
				break;
			case "top":
				j.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; height:' + r * w + 'px"></div>').css({
					position: "relative",
					padding: "0",
					margin: "0"
				}).children().css({
					height: z
				});
				break;
			case "left":
				j.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; width:' + r * x + 'px"></div>').css({
					width: k * x,
					position: "relative",
					overflow: "hidden",
					padding: "0",
					margin: "0"
				}).children().css({
					"float": "left",
					width: y
				});
				break;
			case "leftLoop":
			case "leftMarquee":
				j.children().clone().appendTo(j).clone().prependTo(j), j.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; width:' + r * x + 'px"></div>').css({
					width: 3 * k * x,
					position: "relative",
					overflow: "hidden",
					padding: "0",
					margin: "0",
					left: -k * x
				}).children().css({
					"float": "left",
					width: y
				});
				break;
			case "topLoop":
			case "topMarquee":
				j.children().clone().appendTo(j).clone().prependTo(j), j.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; height:' + r * w + 'px"></div>').css({
					height: 3 * k * w,
					position: "relative",
					padding: "0",
					margin: "0",
					top: -k * w
				}).children().css({
					height: z
				})
			}
			F = function() {
				a.isFunction(c.startFun) && c.startFun(n, i)
			}, G = function() {
				a.isFunction(c.endFun) && c.endFun(n, i)
			}, H = function(b) {
				b.eq(n).find("img").each(function() {
					void 0 !== a(this).attr(l) && a(this).attr("src", a(this).attr(l)).removeAttr(l)
				})
			}, I = function(a) {
				var b, p, s, t;
				if (C != n || a || "leftMarquee" == d || "topMarquee" == d) {
					switch (d) {
					case "fade":
					case "fold":
					case "top":
					case "left":
						n >= i ? n = 0 : 0 > n && (n = i - 1);
						break;
					case "leftMarquee":
					case "topMarquee":
						n >= 1 ? n = 1 : 0 >= n && (n = 0);
						break;
					case "leftLoop":
					case "topLoop":
						b = n - C, i > 2 && b == -(i - 1) && (b = 1), i > 2 && b == i - 1 && (b = -1), p = Math.abs(b * q), n >= i ? n = 0 : 0 > n && (n = i - 1)
					}
					if (F(), null != l && H(j.children()), m && (null != l && H(m), m.hide().eq(n).animate({
						opacity: "show"
					}, o, function() {
						j[0] || G()
					})), k >= r) switch (d) {
					case "fade":
						j.children().stop(!0, !0).eq(n).animate({
							opacity: "show"
						}, o, A, function() {
							G()
						}).siblings().hide();
						break;
					case "fold":
						j.children().stop(!0, !0).eq(n).animate({
							opacity: "show"
						}, o, A, function() {
							G()
						}).siblings().animate({
							opacity: "hide"
						}, o, A);
						break;
					case "top":
						j.stop(!0, !1).animate({
							top: -n * q * w
						}, o, A, function() {
							G()
						});
						break;
					case "left":
						j.stop(!0, !1).animate({
							left: -n * q * x
						}, o, A, function() {
							G()
						});
						break;
					case "leftLoop":
						0 > b ? j.stop(!0, !0).animate({
							left: -(k - p) * x
						}, o, A, function() {
							for (var a = 0; p > a; a++) j.children().last().prependTo(j);
							j.css("left", -k * x), G()
						}) : j.stop(!0, !0).animate({
							left: -(k + p) * x
						}, o, A, function() {
							for (var a = 0; p > a; a++) j.children().first().appendTo(j);
							j.css("left", -k * x), G()
						});
						break;
					case "topLoop":
						0 > b ? j.stop(!0, !0).animate({
							top: -(k - p) * w
						}, o, A, function() {
							for (var a = 0; p > a; a++) j.children().last().prependTo(j);
							j.css("top", -k * w), G()
						}) : j.stop(!0, !0).animate({
							top: -(k + p) * w
						}, o, A, function() {
							for (var a = 0; p > a; a++) j.children().first().appendTo(j);
							j.css("top", -k * w), G()
						});
						break;
					case "leftMarquee":
						s = j.css("left").replace("px", ""), 0 == n ? j.animate({
							left: ++s
						}, 0, function() {
							if (j.css("left").replace("px", "") >= 0) {
								for (var a = 0; k > a; a++) j.children().last().prependTo(j);
								j.css("left", -k * x)
							}
						}) : j.animate({
							left: --s
						}, 0, function() {
							if (2 * -k * x >= j.css("left").replace("px", "")) {
								for (var a = 0; k > a; a++) j.children().first().appendTo(j);
								j.css("left", -k * x)
							}
						});
						break;
					case "topMarquee":
						t = j.css("top").replace("px", ""), 0 == n ? j.animate({
							top: ++t
						}, 0, function() {
							if (j.css("top").replace("px", "") >= 0) {
								for (var a = 0; k > a; a++) j.children().last().prependTo(j);
								j.css("top", -k * w)
							}
						}) : j.animate({
							top: --t
						}, 0, function() {
							if (2 * -k * w >= j.css("top").replace("px", "")) {
								for (var a = 0; k > a; a++) j.children().first().appendTo(j);
								j.css("top", -k * w)
							}
						})
					}
					h.removeClass(c.titOnClassName).eq(n).addClass(c.titOnClassName), C = n, 0 == v && (f.removeClass("nextStop"), e.removeClass("prevStop"), 0 == n ? e.addClass("prevStop") : n == i - 1 && f.addClass("nextStop")), g.html("<span>" + (n + 1) + "</span>/" + i)
				}
			}, I(!0), s && ("leftMarquee" == d || "topMarquee" == d ? (t ? n-- : n++, B = setInterval(I, p), j.hover(function() {
				s && clearInterval(B)
			}, function() {
				s && (clearInterval(B), B = setInterval(I, p))
			})) : (B = setInterval(function() {
				t ? n-- : n++, I()
			}, p), a(this).hover(function() {
				s && clearInterval(B)
			}, function() {
				s && (clearInterval(B), B = setInterval(function() {
					t ? n-- : n++, I()
				}, p))
			}))), "mouseover" == c.trigger ? h.hover(function() {
				n = h.index(this), J = window.setTimeout(I, c.triggerTime)
			}, function() {
				clearTimeout(J)
			}) : h.click(function() {
				n = h.index(this), I()
			}), f.click(function() {
				(1 == v || n != i - 1) && (n++, I())
			}), e.click(function() {
				(1 == v || 0 != n) && (n--, I())
			})
		})
	}
}(jQuery), jQuery.easing.jswing = jQuery.easing.swing, jQuery.extend(jQuery.easing, {
	def: "easeOutQuad",
	swing: function(a, b, c, d, e) {
		return jQuery.easing[jQuery.easing.def](a, b, c, d, e)
	},
	easeInQuad: function(a, b, c, d, e) {
		return d * (b /= e) * b + c
	},
	easeOutQuad: function(a, b, c, d, e) {
		return -d * (b /= e) * (b - 2) + c
	},
	easeInOutQuad: function(a, b, c, d, e) {
		return 1 > (b /= e / 2) ? d / 2 * b * b + c : -d / 2 * (--b * (b - 2) - 1) + c
	},
	easeInCubic: function(a, b, c, d, e) {
		return d * (b /= e) * b * b + c
	},
	easeOutCubic: function(a, b, c, d, e) {
		return d * ((b = b / e - 1) * b * b + 1) + c
	},
	easeInOutCubic: function(a, b, c, d, e) {
		return 1 > (b /= e / 2) ? d / 2 * b * b * b + c : d / 2 * ((b -= 2) * b * b + 2) + c
	},
	easeInQuart: function(a, b, c, d, e) {
		return d * (b /= e) * b * b * b + c
	},
	easeOutQuart: function(a, b, c, d, e) {
		return -d * ((b = b / e - 1) * b * b * b - 1) + c
	},
	easeInOutQuart: function(a, b, c, d, e) {
		return 1 > (b /= e / 2) ? d / 2 * b * b * b * b + c : -d / 2 * ((b -= 2) * b * b * b - 2) + c
	},
	easeInQuint: function(a, b, c, d, e) {
		return d * (b /= e) * b * b * b * b + c
	},
	easeOutQuint: function(a, b, c, d, e) {
		return d * ((b = b / e - 1) * b * b * b * b + 1) + c
	},
	easeInOutQuint: function(a, b, c, d, e) {
		return 1 > (b /= e / 2) ? d / 2 * b * b * b * b * b + c : d / 2 * ((b -= 2) * b * b * b * b + 2) + c
	},
	easeInSine: function(a, b, c, d, e) {
		return -d * Math.cos(b / e * (Math.PI / 2)) + d + c
	},
	easeOutSine: function(a, b, c, d, e) {
		return d * Math.sin(b / e * (Math.PI / 2)) + c
	},
	easeInOutSine: function(a, b, c, d, e) {
		return -d / 2 * (Math.cos(Math.PI * b / e) - 1) + c
	},
	easeInExpo: function(a, b, c, d, e) {
		return 0 == b ? c : d * Math.pow(2, 10 * (b / e - 1)) + c
	},
	easeOutExpo: function(a, b, c, d, e) {
		return b == e ? c + d : d * (-Math.pow(2, -10 * b / e) + 1) + c
	},
	easeInOutExpo: function(a, b, c, d, e) {
		return 0 == b ? c : b == e ? c + d : 1 > (b /= e / 2) ? d / 2 * Math.pow(2, 10 * (b - 1)) + c : d / 2 * (-Math.pow(2, -10 * --b) + 2) + c
	},
	easeInCirc: function(a, b, c, d, e) {
		return -d * (Math.sqrt(1 - (b /= e) * b) - 1) + c
	},
	easeOutCirc: function(a, b, c, d, e) {
		return d * Math.sqrt(1 - (b = b / e - 1) * b) + c
	},
	easeInOutCirc: function(a, b, c, d, e) {
		return 1 > (b /= e / 2) ? -d / 2 * (Math.sqrt(1 - b * b) - 1) + c : d / 2 * (Math.sqrt(1 - (b -= 2) * b) + 1) + c
	},
	easeInElastic: function(a, b, c, d, e) {
		var f = 1.70158,
			g = 0,
			h = d;
		return 0 == b ? c : 1 == (b /= e) ? c + d : (g || (g = .3 * e), Math.abs(d) > h ? (h = d, f = g / 4) : f = g / (2 * Math.PI) * Math.asin(d / h), -(h * Math.pow(2, 10 * (b -= 1)) * Math.sin(2 * (b * e - f) * Math.PI / g)) + c)
	},
	easeOutElastic: function(a, b, c, d, e) {
		var f = 1.70158,
			g = 0,
			h = d;
		return 0 == b ? c : 1 == (b /= e) ? c + d : (g || (g = .3 * e), Math.abs(d) > h ? (h = d, f = g / 4) : f = g / (2 * Math.PI) * Math.asin(d / h), h * Math.pow(2, -10 * b) * Math.sin(2 * (b * e - f) * Math.PI / g) + d + c)
	},
	easeInOutElastic: function(a, b, c, d, e) {
		var f = 1.70158,
			g = 0,
			h = d;
		return 0 == b ? c : 2 == (b /= e / 2) ? c + d : (g || (g = 1.5 * .3 * e), Math.abs(d) > h ? (h = d, f = g / 4) : f = g / (2 * Math.PI) * Math.asin(d / h), 1 > b ? -.5 * h * Math.pow(2, 10 * (b -= 1)) * Math.sin(2 * (b * e - f) * Math.PI / g) + c : .5 * h * Math.pow(2, -10 * (b -= 1)) * Math.sin(2 * (b * e - f) * Math.PI / g) + d + c)
	},
	easeInBack: function(a, b, c, d, e, f) {
		return void 0 == f && (f = 1.70158), d * (b /= e) * b * ((f + 1) * b - f) + c
	},
	easeOutBack: function(a, b, c, d, e, f) {
		return void 0 == f && (f = 1.70158), d * ((b = b / e - 1) * b * ((f + 1) * b + f) + 1) + c
	},
	easeInOutBack: function(a, b, c, d, e, f) {
		return void 0 == f && (f = 1.70158), 1 > (b /= e / 2) ? d / 2 * b * b * (((f *= 1.525) + 1) * b - f) + c : d / 2 * ((b -= 2) * b * (((f *= 1.525) + 1) * b + f) + 2) + c
	},
	easeInBounce: function(a, b, c, d, e) {
		return d - jQuery.easing.easeOutBounce(a, e - b, 0, d, e) + c
	},
	easeOutBounce: function(a, b, c, d, e) {
		return 1 / 2.75 > (b /= e) ? 7.5625 * d * b * b + c : 2 / 2.75 > b ? d * (7.5625 * (b -= 1.5 / 2.75) * b + .75) + c : 2.5 / 2.75 > b ? d * (7.5625 * (b -= 2.25 / 2.75) * b + .9375) + c : d * (7.5625 * (b -= 2.625 / 2.75) * b + .984375) + c
	},
	easeInOutBounce: function(a, b, c, d, e) {
		return e / 2 > b ? .5 * jQuery.easing.easeInBounce(a, 2 * b, 0, d, e) + c : .5 * jQuery.easing.easeOutBounce(a, 2 * b - e, 0, d, e) + .5 * d + c
	}
}), jQuery.cookie = function(a, b, c) {
	var d, e, f, g, h, i, j, k, l;
	if ("undefined" == typeof b) {
		if (i = null, document.cookie && "" != document.cookie) for (j = document.cookie.split(";"), k = 0; k < j.length; k++) if (l = jQuery.trim(j[k]), l.substring(0, a.length + 1) == a + "=") {
			i = decodeURIComponent(l.substring(a.length + 1));
			break
		}
		return i
	}
	c = c || {}, null === b && (b = "", c.expires = -1), d = "", c.expires && ("number" == typeof c.expires || c.expires.toUTCString) && ("number" == typeof c.expires ? (e = new Date, e.setTime(e.getTime() + 1e3 * 60 * 60 * 24 * c.expires)) : e = c.expires, d = "; expires=" + e.toUTCString()), f = c.path ? "; path=" + c.path : "", g = c.domain ? "; domain=" + c.domain : "", h = c.secure ? "; secure" : "", document.cookie = [a, "=", encodeURIComponent(b), d, f, g, h].join("")
}, lazyloadImg = function() {
	this.datas = {
		lazy_info: {
			type: "lazyload-type",
			url: "lazyload-url"
		},
		lazy_data: {}
	}
}, lazyloadImg.prototype = {
	init: function() {
		this.bindEvent(), this.checkImg($(document).scrollTop() + $(window).height())
	},
	checkImg: function(a) {
		var b = null;
		$("img[data-lazyload-type]").each(function() {
			b = $(this), "hand" !== b.data("lazyload-type") && b.offset().top <= a && (b.attr("src", b.data("lazyload-src")), b.removeAttr("data-lazyload-type"), b.removeAttr("data-lazyload-src"))
		})
	},
	bindEvent: function() {
		var a = this;
		$(window).on("scroll", function() {
			a.checkImg($(document).scrollTop() + $(window).height())
		})
	}
}, (new lazyloadImg).init();
function vip_callback($vod_id, $vod_sid, $vod_pid, $status, $trysee, $tips){
		if($status != 200){
			if($trysee > 0){
				window.setTimeout(function(){
					$.get(Root+'index.php?s=home-vod-vip-type-trysee-id-'+$vod_id+'-sid-'+$vod_sid+'-pid-'+$vod_pid, function(html){
						$("#xianyucms-player-vip").html(html);
					},'html');
				},1000*60*$trysee);
			}else{
				$('#xianyucms-player-vip .xianyucms-player-box').html($tips);
				$('#xianyucms-player-vip .xianyucms-player-iframe').hide();
			}
			//播放你密码
			$('body').on("click","#player-pwd",function(){
				$(this).html('Loading...');
				$pwd=$(".password").val();
				$.get(Root+'index.php?s=home-vod-vip-type-pwd-id-'+$vod_id+'-sid-'+$vod_sid+'-pid-'+$vod_pid+'-pwd-'+$pwd, function(json){
					if(json.status == 200){
						location.reload();
					}else{
						$(this).html('播放');
						alert('密码错误或失效,请重新回复');
					}
				},'json');
			});	
			//支付影币按钮
			$('body').on("click","#player-price",function(){
				$(this).html('Loading...');
				$.get(Root+'index.php?s=home-vod-vip-type-ispay-id-'+$vod_id+'-sid-'+$vod_sid+'-pid-'+$vod_pid, function(json){
					if(json.status == 200){
						location.reload();
					}else if(json.status == 500 || json.status == 501){
						login_form();
					}else{
						$('#xianyucms-player-vip').html(json.info);
					}
				},'json');
			});				
		}else{
			//拥有VIP观看权限
		}
}
$(document).ready(function(){
$('body').on("click","#loginbarx,#user-login,#player-login",function(){												 
	if (!checkcookie()) {
		login_form();
		return false;
	}
});							   
//订单付款界面						   
$('body').on("click","#user-score-payment",function(){	
	 payment();
});
//购买VIP界面
$('body').on("click","#ispay-vip",function(){
    $.colorbox({
        href: Root+'index.php?s=user-buy-index',
    });
});
//支付VIP影币
$('body').on("click","#pay_vip",function(){								 
   $(".form-horizontal").qiresub({
			curobj: $("#pay_vip"),
			txt: "数据提交中,请稍后...",
			onsucc: function(a) {
				if($.hidediv(a),parseInt(a["rcode"])  > 0) {
					qr.gu({
						ubox: "unm",
						rbox: "innermsg",
						h3: "h3",
						logo: "userlogo"
					});
				$("#cboxClose").trigger("click")
				 location.reload();
				}
				if($.hidediv(a),parseInt(a["rcode"]) == -2){
				 payment();
				}
				else - 3 == parseInt(a["rcode"])
			}
		}).post({
			url: Root + "index.php?s=user-buy-index"
		}), !1;
	
});
//购买VIP界面
$('body').on("click","#pay_card",function(){
     payment_card();
});
$('body').on("click","#payment_card",function(){								 
   $(".form-horizontal").qiresub({
			curobj: $("#payment_card"),
			txt: "数据提交中,请稍后...",
			onsucc: function(a) {
				if($.hidediv(a),parseInt(a["code"])  > 0) {
					qr.gu({
						ubox: "unm",
						rbox: "innermsg",
						h3: "h3",
						logo: "userlogo"
					});
				$("#cboxClose").trigger("click")
				 location.reload();
				}
				if($.hidediv(a),parseInt(a["code"]) == -2){
				 payment();
				}
				else - 3 == parseInt(a["code"])
			}
		}).post({
			url: Root + "index.php?s=user-payment-card"
		}), !1;
	
});


})
function payment(){
	 $.colorbox({
        href: Root+'index.php?s=user-payment-index',
    });
}
function payment_card(){
	 $.colorbox({
        href: Root+'index.php?s=user-payment-card',
    });
}
function player_iframe(){
		if($("#xianyucms-player-vip").length>0){
			self.location.reload();
		}
}