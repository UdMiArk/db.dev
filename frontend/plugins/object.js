const _Object = Object;

export function isPlainObject(val) {
	let proto;
	return Boolean(val && typeof val === "object" && (!(proto = _Object.getPrototypeOf(val)) || proto === _Object.prototype));
}

export function isArray(val) {
	return Boolean(val && val instanceof Array);
}

/**
 * Deeply freezes plain object, ignores props if their value is not plain object
 * TODO: remake without recursion
 * @param {object} obj
 * @param {boolean} processFrozen
 * @returns {*}
 */
export function deepFreeze(obj, processFrozen = false) {
	if (isPlainObject(obj) && (!_Object.isFrozen(obj) || processFrozen)) {
		_Object.freeze(obj);

		for (const key of _Object.keys(obj)) {
			deepFreeze(obj[key]);
		}
	}

	return obj;
}

/**
 *
 * @param {object} dst - Object to be extended
 * @param {object} src - Source of properties
 * @param {boolean} [noOverwrite=false] - Do not overwrite dst property if it already exists and !== undefined
 * @param {boolean} [notRecursive=false] - Do not extend props received from src
 * @returns {*}
 */
export function objectExtend(dst, src, noOverwrite = false, notRecursive = false) {
	let i, iMax, propName, props;

	const recursive = !notRecursive;
	const overwrite = !noOverwrite;

	props = _Object.keys(src);
	for (i = 0, iMax = props.length; i < iMax; i++) {
		propName = props[i];
		if (dst[propName] !== src[propName] && (overwrite || dst[propName] === undefined)) {
			if ((isPlainObject(src[propName]) || isArray(src[propName])) && (recursive || !_Object.isFrozen(src[propName]))) {
				if (recursive && isPlainObject(src[propName]) && isPlainObject(dst[propName])) {
					dst[propName] = objectExtend(dst[propName], src[propName], overwrite, recursive);
				} else {
					// Create copy of source property value and assign it to destination property
					dst[propName] = objectCopy(src[propName]);
				}
			} else {
				dst[propName] = src[propName];
			}
		}
	}

	return dst;
}

export function objectCopy(src) {
	if (isPlainObject(src)) {
		return objectExtend({__proto__: src.__proto__ || null}, src);
	} else if (isArray(src)) {
		return src.map(x => objectCopy(x));
	} else {
		return src;
	}
}