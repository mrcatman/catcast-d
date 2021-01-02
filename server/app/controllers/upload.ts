import { Picture } from '../models/Picture';
import {ServerInstance} from "../types";
const util = require('util');
const path = require('path');
const fs = require('fs')
const { pipeline } = require('stream')
const pump = util.promisify(pipeline)

async function routes (fastify: ServerInstance, options) {
    fastify.post('/pictures', {
        preValidation: [fastify.authenticate]
    }, async (req, res) => {
        const data = await req.file();
        const now = new Date();
        const datePath = now.getFullYear() + '/' + now.getMonth() + '/' + now.getDay();
        const uploadsPath = '/uploads/' + datePath;

        const uploadsDir = path.dirname(require.main.filename) + uploadsPath;
        fs.mkdirSync(uploadsDir, { recursive: true });

        const extension = data.filename.split('.')[data.filename.split('.').length - 1];

        const fileName = Date.now().toString(36) + Math.random().toString(36).substring(2); // make UUID
        const fullName = fileName + '.' + extension;

        const fullPath = uploadsDir + '/' + fullName;

        await pump(data.file, fs.createWriteStream(fullPath));

        let picture = new Picture();
         picture.user_id = req.user.id;
        picture.path = uploadsPath + '/' + fullName;
        await picture.save();
        picture.setComputed();

        res.send({picture});
    })
}

module.exports = routes;
