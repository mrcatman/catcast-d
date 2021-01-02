import {Validator, ValidatorError} from "../types";

class MinLengthValidator implements Validator{
    minLength: number;
    constructor(minLength) {
        this.minLength = minLength;
    }

    async validate(value): Promise<any> {
        if (typeof value === 'string' && value.trim().length >= this.minLength) {
            return value;
        } else {
            return new ValidatorError(['forms.errors.min_length', this.minLength]);
        }
    }
}

module.exports = MinLengthValidator;
