/**
 * Leaflet.PolylineDecorator plugin
 * Supporting L.PolylineDecorator, L.polylineDecorator, and L.Symbol.arrowHead
 */
(function () {
    var L = (typeof window !== 'undefined' ? window.L : (typeof global !== 'undefined' ? global.L : null));
    if (!L) return;
    if (L.PolylineDecorator && L.Symbol && L.Symbol.arrowHead) return;

    L.Symbol = L.Symbol || {};

    L.Symbol.ArrowHead = L.Class.extend({
        options: {
            polygon: true,
            pixelSize: 10,
            headAngle: 60,
            pathOptions: {
                stroke: false,
                weight: 1
            }
        },
        initialize: function (options) {
            L.Util.setOptions(this, options);
        },
        buildSymbol: function (dirPoint, latLngs, map, index, total) {
            var opts = this.options;
            var angle = dirPoint.heading;
            var headAngle = ((opts.headAngle || 60) * Math.PI) / 180;
            var halfAngle = headAngle / 2;
            var size = opts.pixelSize || 10;

            var pt = map.project(dirPoint.latLng);
            var tip = pt;
            var left = L.point(
                tip.x - size * Math.cos(angle - halfAngle),
                tip.y - size * Math.sin(angle - halfAngle)
            );
            var right = L.point(
                tip.x - size * Math.cos(angle + halfAngle),
                tip.y - size * Math.sin(angle + halfAngle)
            );

            var tipLL = map.unproject(tip);
            var leftLL = map.unproject(left);
            var rightLL = map.unproject(right);

            if (opts.polygon) {
                return L.polygon([tipLL, leftLL, rightLL], opts.pathOptions);
            } else {
                return L.polyline([leftLL, tipLL, rightLL], opts.pathOptions);
            }
        }
    });

    L.Symbol.arrowHead = function (options) {
        return new L.Symbol.ArrowHead(options);
    };

    L.PolylineDecorator = L.FeatureGroup.extend({
        options: {
            patterns: []
        },
        initialize: function (paths, options) {
            L.FeatureGroup.prototype.initialize.call(this);
            L.Util.setOptions(this, options);
            this._paths = Array.isArray(paths) ? paths : [paths];
            this._patterns = this.options.patterns || [];
        },
        onAdd: function (map) {
            this._map = map;
            L.FeatureGroup.prototype.onAdd.call(this, map);
            this._draw();
            map.on('zoomend moveend resize', this._draw, this);
        },
        onRemove: function (map) {
            map.off('zoomend moveend resize', this._draw, this);
            L.FeatureGroup.prototype.onRemove.call(this, map);
        },
        setPaths: function (paths) {
            this._paths = Array.isArray(paths) ? paths : [paths];
            this._draw();
            return this;
        },
        setPatterns: function (patterns) {
            this._patterns = patterns;
            this._draw();
            return this;
        },
        _draw: function () {
            if (!this._map) return;
            this.clearLayers();
            var map = this._map;

            for (var p = 0; p < this._paths.length; p++) {
                var path = this._paths[p];
                var latlngs = path.getLatLngs ? path.getLatLngs() : path;
                if (!latlngs || latlngs.length < 2) continue;

                if (Array.isArray(latlngs[0])) {
                    latlngs = latlngs.flat();
                }

                var pts = [];
                for (var i = 0; i < latlngs.length; i++) {
                    pts.push(map.project(latlngs[i]));
                }

                var segLengths = [];
                var totalPixelLen = 0;
                for (var i = 0; i < pts.length - 1; i++) {
                    var d = pts[i].distanceTo(pts[i + 1]);
                    segLengths.push(d);
                    totalPixelLen += d;
                }
                if (totalPixelLen === 0) continue;

                for (var pi = 0; pi < this._patterns.length; pi++) {
                    var pattern = this._patterns[pi];
                    var symbol = pattern.symbol;
                    if (!symbol || typeof symbol.buildSymbol !== 'function') continue;

                    var offset = typeof pattern.offset === 'string' && pattern.offset.indexOf('%') !== -1
                        ? (totalPixelLen * parseFloat(pattern.offset)) / 100
                        : (pattern.offset || 0);

                    var repeat = typeof pattern.repeat === 'string' && pattern.repeat.indexOf('%') !== -1
                        ? (totalPixelLen * parseFloat(pattern.repeat)) / 100
                        : (pattern.repeat || 0);

                    var step = repeat > 0 ? repeat : totalPixelLen;
                    var curDist = offset;

                    while (curDist < totalPixelLen) {
                        var acc = 0;
                        for (var si = 0; si < segLengths.length; si++) {
                            if (acc + segLengths[si] >= curDist) {
                                var segOffset = curDist - acc;
                                var ratio = segLengths[si] > 0 ? segOffset / segLengths[si] : 0;
                                var p1 = pts[si];
                                var p2 = pts[si + 1];
                                var interp = L.point(
                                    p1.x + (p2.x - p1.x) * ratio,
                                    p1.y + (p2.y - p1.y) * ratio
                                );
                                var heading = Math.atan2(p2.y - p1.y, p2.x - p1.x);
                                var dirPoint = {
                                    latLng: map.unproject(interp),
                                    heading: heading
                                };
                                var symLayer = symbol.buildSymbol(dirPoint, latlngs, map, si, segLengths.length);
                                if (symLayer) {
                                    this.addLayer(symLayer);
                                }
                                break;
                            }
                            acc += segLengths[si];
                        }
                        curDist += step;
                        if (repeat <= 0) break;
                    }
                }
            }
        }
    });

    L.polylineDecorator = function (paths, options) {
        return new L.PolylineDecorator(paths, options);
    };
})();
