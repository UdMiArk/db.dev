const
	RESOURCE_STATUS_AWAITING = 0,
	RESOURCE_STATUS_APPROVED = 10,
	RESOURCE_STATUS_REJECTED = 90;

export const RESOURCE_STATUSES = [
	Object.freeze({value: RESOURCE_STATUS_AWAITING, label: "Ожидание"}),
	Object.freeze({value: RESOURCE_STATUS_APPROVED, label: "Утвержден", icon: "check"}),
	Object.freeze({value: RESOURCE_STATUS_REJECTED, label: "Отклонен", icon: "cancel"})
];
RESOURCE_STATUSES.AWAITING = RESOURCE_STATUS_AWAITING;
RESOURCE_STATUSES.APPROVED = RESOURCE_STATUS_APPROVED;
RESOURCE_STATUSES.REJECTED = RESOURCE_STATUS_REJECTED;
RESOURCE_STATUSES.indexed = Object.freeze(RESOURCE_STATUSES.reduce((r, x) => (r[x.value] = x, r), {__proto__: null}));
Object.freeze(RESOURCE_STATUSES);

const
	RESATTR_TYPE_FILE = 10,
	RESATTR_TYPE_FILES = 20;

export const RESATTR_TYPES = [
	Object.freeze({value: RESATTR_TYPE_FILE, label: "Файл"}),
	Object.freeze({value: RESATTR_TYPE_FILES, label: "Набор файлов"})
];
RESATTR_TYPES.FILE = RESATTR_TYPE_FILE;
RESATTR_TYPES.FILES = RESATTR_TYPE_FILES;
RESATTR_TYPES.indexed = Object.freeze(RESATTR_TYPES.reduce((r, x) => (r[x.value] = x, r), {__proto__: null}));
Object.freeze(RESATTR_TYPES);
