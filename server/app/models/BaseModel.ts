import {
    BaseEntity
} from "typeorm";
import { FastifyRequest } from 'fastify'

export type values = {
    [key: string]: any;
}

export class BaseModel extends BaseEntity {

    fill(values: values){
        for (let key in values) {
            this[key] = values[key];
        }
    }

    static async paginate(props, req) {
        if (!props.where && Object.keys(props).length > 0 && !Object.keys(props).includes('relations')) {
            props = {
                where: props
            }
        }
        const total = await this.count(props);
        const count = parseInt(req.body && req.body.count ? req.body.count : (req.query && req.query.count ? req.query.count : 12));
        let page = parseInt(req.body && req.body.page ? req.body.page : (req.query && req.query.page ? req.query.page : 1));
        if (page < 1) {
            page = 1;
        }
        let pagesCount = Math.ceil(total / count);
        if (!pagesCount) {
            pagesCount = 0;
        }
        props.take = count;
        props.skip = count * (page - 1);
        const list = await this.find(props);
        return {
            total,
            list,
            page,
            pagesCount
        }
    }

}
