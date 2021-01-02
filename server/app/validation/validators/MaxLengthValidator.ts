
import {Validator, ValidatorError} from "../types";

class MaxLengthValidator implements Validator{
    maxLength: number;
    constructor(maxLength) {
        this.maxLength = maxLength;
    }


    async validate(value): Promise<any> {
        if (!value) {
            return null;
        }
        if (typeof value === 'string' && value.trim().length <= this.maxLength) {
            return value;
        }
        return new ValidatorError(['forms.errors.max_length', this.maxLength]);
    }
}

module.exports = MaxLengthValidator;
