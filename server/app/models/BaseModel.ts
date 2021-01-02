import {
    BaseEntity
} from "typeorm";

export type values = {
    [key: string]: any;
}

export class BaseModel extends BaseEntity {

    fill(values: values){
        for (let key in values) {
            this[key] = values[key];
        }
    }

}
