import {Validator, ValidatorError} from "../types";

export class BaseRegexValidator implements Validator{

    readonly regex: RegExp;

    get error() {
        return 'forms.errors.wrong_type';
    }

    async validate(value): Promise<any> {
        if (typeof value === 'string' && this.regex.test(value)) {
            return value;
        } else {
            return new ValidatorError(this.error);
        }
    }

}
