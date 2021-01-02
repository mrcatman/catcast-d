import {ValidationRules} from "../validation/types";

const NotEmptyValidator = require('../validation/validators/NotEmptyValidator');
const MaxLengthValidator = require('../validation/validators/MaxLengthValidator');
const PictureValidator = require('../validation/validators/PictureValidator');
const AlphanumericWithSymbolsValidator = require('../validation/validators/AlphanumericWithSymbolsValidator');

export function getBaseChannelValidators(): ValidationRules {
    return {
        name: [new NotEmptyValidator()],
        url: [new AlphanumericWithSymbolsValidator()],
        description: [new MaxLengthValidator(500)],
        logo: [new PictureValidator(false)],
    };
}
