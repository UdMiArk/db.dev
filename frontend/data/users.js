const
	USER_STATUS_REGISTERED = 0,
	USER_STATUS_DISABLED = 100;

export const USER_STATUSES = [
	Object.freeze({value: USER_STATUS_REGISTERED, label: "Зарегестрирован"}),
	Object.freeze({value: USER_STATUS_DISABLED, label: "Отключен", icon: "cancel"})
];
USER_STATUSES.REGISTERED = USER_STATUS_REGISTERED;
USER_STATUSES.DISABLED = USER_STATUS_DISABLED;
USER_STATUSES.indexed = Object.freeze(USER_STATUSES.reduce((r, x) => (r[x.value] = x, r), {__proto__: null}));
Object.freeze(USER_STATUSES);
