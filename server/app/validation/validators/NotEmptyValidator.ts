import {Validator, ValidatorError} from "../types";

class NotEmptyValidator implements Validator{

    async validate(value): Promise<any> {
        if (typeof value === 'string' && value.trim().length > 0) {
            return value;
        } else {
            return new ValidatorError('forms.errors.field_required');
        }
    }

}

module.exports = NotEmptyValidator;
