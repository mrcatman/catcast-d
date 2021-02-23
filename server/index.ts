import fastify, { FastifyInstance } from 'fastify'
const path = require('path');
import { validate } from "./app/validation/validate";
import {User} from "./app/models/User";
import { config } from './app/config'

interface FastifyInstanceExtended extends FastifyInstance {
    getDefaultJsonParser: Function
}

const server = fastify({logger: false}) as any as FastifyInstanceExtended;

server.register(require('fastify-websocket'))

server.register(require('fastify-cookie'), {
    secret: config('secrets.cookie'),
    parseOptions: {}
});
server.register(require('fastify-jwt'), {
    secret: config('secrets.jwt'),
    cookie: {
        cookieName: 'token'
    }
});

server.register(require('fastify-typeorm'), {
    ...config('database'),
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
server.register(require('fastify-static'), {
    root: path.join(__dirname, 'static'),
    prefix: '/static/',
    decorateReply: false
})
server.register(require('fastify-formbody'));
server.register(require('fastify-url-data'));
server.register(require('fastify-raw-body'), {
    field: 'rawBody',
    global: false,
})

server.addContentTypeParser('application/activity+json', { parseAs: 'string' }, server.getDefaultJsonParser('ignore', 'ignore') as any)

server.decorate("authenticate", async function(request, reply) {
    try {
        await request.jwtVerify()
        request.user = await User.findOne({id: request.user.id});
    } catch (err) {
        reply.send(err)
    }
})

server.decorate("authenticate_optional", async function(request) {
    try {
        await request.jwtVerify()
        request.user = await User.findOne({id: request.user.id});
    } catch (err) {

    }
})
server.decorate("authenticate_admin", async function(request, reply) {
    try {
        await request.jwtVerify()
        request.user = await User.findOne({id: request.user.id});
        if (!request.user.isAdmin()) {
            throw new Error("Access error");
        }
    } catch (err) {
        reply.send(err)
    }
})

server.decorate('validate', validate);


(async() => {
    const controllerPaths = {
        'site': '/api/site',
        'channels': '/api/channels',
        'auth': '/api/auth',
        'upload': '/api/upload',
        'stream': '/api/stream',
        'webfinger': '.well-known',
        'federation': '/api/federation',
        'chat': '/api/chat'
    };
    for (let controller in controllerPaths) {
        server.register(await import('./app/controllers/' + controller), {prefix: controllerPaths[controller]})
    }


    server.listen(config('server.port'), '0.0.0.0', function (err, address) {
        if (err) {
            console.log(err)
        }
        server.log.info(`server listening on ${address}`)
    });

})()
