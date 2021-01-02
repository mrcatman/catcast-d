export interface Validator {
    validate(value: any): Promise<any>;
}

export interface ValidationRules {
    [key: string]: Array<Validator>;
}

export class ValidatorError {
    error: string | [string, number];
    constructor(error: string | [string, number]) {
        this.error = error;
    }
}
