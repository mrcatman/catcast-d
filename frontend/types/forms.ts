export enum Warning {
  FIELD_REQUIRED = 'forms.errors.field_required',
}

export type FormValues = {
  [key: string]: any;
}
export type FormErrors = {
  [key: string]: string | Array<string> | null;
}
export type FormWarnings = {
  [key: string]: string | Array<Warning> | null;
}
