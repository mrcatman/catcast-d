import {Validator, ValidatorError} from "../types";

class BooleanValidator implements Validator{

    async validate(value): Promise<any> {
        if (Array.isArray(value)) {
            return value;
        } else {
            return new ValidatorError('forms.errors.field_array');
        }
    }

}

module.exports = BooleanValidator;
