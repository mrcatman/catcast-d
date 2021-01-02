import {BaseRegexValidator} from "./BaseRegexValidator";

class AlphanumericValidator extends BaseRegexValidator {

    get regex() {
        return /^[a-z0-9]+$/i;
    }

    get error() {
        return 'forms.errors.alphanumeric';
    }

}

module.exports = AlphanumericValidator;
