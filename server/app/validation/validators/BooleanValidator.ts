import {Validator, ValidatorError} from "../types";

class BooleanValidator implements Validator{

    async validate(value): Promise<any> {
        if (typeof value === 'boolean') {
            return value;
        } else {
            return new ValidatorError('forms.errors.field_boolean');
        }
    }

}

module.exports = BooleanValidator;
