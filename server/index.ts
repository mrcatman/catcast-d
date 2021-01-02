import fastify from "fastify";
const path = require('path');
import { validate } from "./app/validation/validate";
import {User} from "./app/models/User";

const server = fastify({logger: false});
const config = require('./app/config');

server.register(require('fastify-cookie'), {
    secret: "my-secret", // for cookies signature
    parseOptions: {}     // options for parsing cookies
});
server.register(require('fastify-jwt'), {
    secret: 'foobar',
    cookie: {
        cookieName: 'token'
    }
});
server.register(require('fastify-typeorm'), {
    ...config.database,
    synchronize: true,
    extra: {
        "charset": "utf8mb4_unicode_ci"
    },
    entities: [
        "app/models/*.ts"
    ],
});
server.register(require('fastify-cors'), {
    origin: 'http://localhost:3000',
    credentials: true
});
server.register(require('fastify-multipart'));
server.register(require('fastify-static'), {
    root: path.join(__dirname, 'uploads'),
    prefix: '/uploads/',
})
server.register(require('fastify-formbody'))

server.decorate("authenticate", async function(request, reply) {
    try {
        await request.jwtVerify()
        request.user = await User.findOne({id: request.user.id});
    } catch (err) {
        reply.send(err)
    }
})

server.decorate('validate', validate);
server.decorate('config', config);

(async() => {
    const controllerPaths = {
        'channels': '/api/channels',
        'auth': '/api/auth',
        'upload': '/api/upload',
        'stream': '/api/stream',
        'webfinger': '.well-known',
        'federation': '/api/federation'
    };
    for (let controller in controllerPaths) {
        server.register(await import('./app/controllers/' + controller), {prefix: controllerPaths[controller]})
    }


    server.listen(4002, function (err, address) {
        if (err) {
            console.log(err)
        }
        server.log.info(`server listening on ${address}`)
    });

})()
