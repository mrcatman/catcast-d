
import {Validator, ValidatorError} from "../types";
import {User} from "../../models/User";
import {Picture} from "../../models/Picture";

class PictureValidator implements Validator{
    required: boolean;
    constructor(required = false) {
        this.required = required;
    }

    async validate(value): Promise<any> {
        let noValue = this.required ? new ValidatorError('forms.errors.upload_picture') : null;
        if (!value) {
            return noValue;
        }
        let id;
        if (typeof value === 'number' && value > 0) {
            id = value;
        }
        if (value.id) {
            id = value.id;
        }
        if (!id) {
            return noValue;
        }
        let picture = await Picture.findOne({id});
        if (picture) {
            return picture;
        }
        return noValue;
    }
}

module.exports = PictureValidator;
