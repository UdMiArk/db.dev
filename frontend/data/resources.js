const
	RESOURCE_STATUS_AWAITING = 0,
	RESOURCE_STATUS_APPROVED = 10,
	RESOURCE_STATUS_REJECTED = 90;

export const RESOURCE_STATUS = [
	Object.freeze({value: RESOURCE_STATUS_AWAITING, label: "Ожидание"}),
	Object.freeze({value: RESOURCE_STATUS_APPROVED, label: "Утвержден", icon: "check"}),
	Object.freeze({value: RESOURCE_STATUS_REJECTED, label: "Отклонен", icon: "cancel"})
];
RESOURCE_STATUS.AWAITING = RESOURCE_STATUS_AWAITING;
RESOURCE_STATUS.APPROVED = RESOURCE_STATUS_APPROVED;
RESOURCE_STATUS.REJECTED = RESOURCE_STATUS_REJECTED;
RESOURCE_STATUS.indexed = Object.freeze(RESOURCE_STATUS.reduce((r, x) => (r[x.value] = x, r), {__proto__: null}));
Object.freeze(RESOURCE_STATUS);

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

const
	ARCHIVE_STATUS_NOT = 0,
	ARCHIVE_STATUS_IN = 1,
	ARCHIVE_STATUS_AWAITING_IN = 100,
	ARCHIVE_STATUS_AWAITING_NOT = 101;

export const ARCHIVE_STATUS = [
	Object.freeze({value: ARCHIVE_STATUS_NOT, label: "Не в архиве"}),
	Object.freeze({value: ARCHIVE_STATUS_IN, label: "В архиве", icon: "zip-box"}),
	Object.freeze({value: ARCHIVE_STATUS_AWAITING_IN, label: "Ожидает архивацию", icon: "alarm"}),
	Object.freeze({value: ARCHIVE_STATUS_AWAITING_NOT, label: "Ожидает распаковки", icon: "alarm"})
];
ARCHIVE_STATUS.NOT_ARCHIVED = ARCHIVE_STATUS_NOT;
ARCHIVE_STATUS.ARCHIVED = ARCHIVE_STATUS_IN;
ARCHIVE_STATUS.AWAITING_ARCHIVATION = ARCHIVE_STATUS_AWAITING_IN;
ARCHIVE_STATUS.AWAITING_DEARCHIVATION = ARCHIVE_STATUS_AWAITING_NOT;
ARCHIVE_STATUS.indexed = Object.freeze(ARCHIVE_STATUS.reduce((r, x) => (r[x.value] = x, r), {__proto__: null}));
Object.freeze(ARCHIVE_STATUS);
