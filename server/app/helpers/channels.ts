import {ValidationRules} from "../validation/types";
import { Channel } from '../models/Channel'
import { Follower } from '../models/Follower'
import { User } from '../models/User'

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


