import {BaseRegexValidator} from "./BaseRegexValidator";

class AlphanumericWithSymbolsValidator extends BaseRegexValidator {

    get regex() {
        return /^[a-z0-9.\-_]+$/i;
    }

    get error() {
        return 'forms.errors.alphanumeric_with_symbols';
    }

}

module.exports = AlphanumericWithSymbolsValidator;
