import {ValidationRules, ValidatorError} from "./types";
interface ValidatedData {
    [key: string]: String | Number
}
async function validate(request, rules: ValidationRules) : Promise<ValidatedData>  {
    const body = request.body, query = request.query;
    let errors = {};
    let hasErrors = false;
    let data = {};
    for (let key in rules) {
        let fieldValue = (body && body[key] !== 'undefined') ? body[key] : (query && query[key] !== 'undefined' ? query[key] : null);
        if (rules[key].length === 0) {
            data[key] = fieldValue;
        } else {
            for (let validator of rules[key]) {
                let result = await validator.validate(fieldValue);
                if (result instanceof ValidatorError) {
                    hasErrors = true;
                    if (!errors[key]) {
                        errors[key] = [];
                    }
                    errors[key].push(result.error);
                } else {
                    data[key] = result;
                }
            }
        }
    }
    if (hasErrors) {
        throw {
            status: 422,
            errors
        };
    }
    return data as ValidatedData;
}

export { validate }
