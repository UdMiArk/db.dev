import dateFormat from "date-fns/format";
import dateParse from "date-fns/parse";

const
	SERVER_FORMAT = "yyyy-MM-dd HH:mm:ss",
	SERVER_FORMAT_NO_TIME = "yyyy-MM-dd",
	TIMED_FORMAT_CHECK = " ";

export function today() {
	const result = new Date();
	result.setHours(0, 0, 0, 0);
	return result;
}

/**
 * @param {Date} date
 * @param {boolean} [withTime=false]
 * @return {string}
 */
export function formatServerDate(date, withTime = false) {
	return dateFormat(date, withTime ? SERVER_FORMAT : SERVER_FORMAT_NO_TIME);
}

/**
 * @param {string} date
 * @return {Date}
 */
export function parseServerDate(date) {
	return date ? dateParse(
		date,
		date.includes(TIMED_FORMAT_CHECK)
			? SERVER_FORMAT
			: SERVER_FORMAT_NO_TIME,
		today()
	) : null;
}

export function formatDate(date) {
	return date ? dateFormat(date, "dd.MM.yyyy") : null;
}

export function formatDateTime(date) {
	return dateFormat(date, "dd.MM.yyyy HH:mm");
}

export function formatTime(date) {
	return dateFormat(date, "HH:mm");
}

export default {
	formatServerDate,
	parseServerDate,
	formatDate,
	formatTime,
	formatDateTime,
	install(Vue, options) {
		Vue.filter("date", formatDate);
		Vue.filter("time", formatTime);
		Vue.filter("dateTime", formatDateTime);
		Vue.filter("parseDate", parseServerDate);
	}
};