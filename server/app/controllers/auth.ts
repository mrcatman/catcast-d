import {User} from "../models/User";
import {ServerInstance} from "../types";
import {generateKeys} from "../federation/crypto";

const argon2 = require('argon2');
const NotEmptyValidator = require('../validation/validators/NotEmptyValidator');
const MinLengthValidator = require('../validation/validators/MinLengthValidator');
const MaxLengthValidator = require('../validation/validators/MaxLengthValidator');
const PictureValidator = require('../validation/validators/PictureValidator');
const AlphanumericWithSymbolsValidator = require('../validation/validators/AlphanumericWithSymbolsValidator');
const EmailValidator = require('../validation/validators/EmailValidator');


async function routes (fastify: ServerInstance, options) {

    fastify.get('/me',  async (req, res): Promise<any> => {
        let userInstance = null;
        try {
            await req.jwtVerify()
            userInstance = await User.findOne({id: req.user.id});
        } catch (e) {

        }
        if (!userInstance) {
            userInstance = null;
        }
        res.send({
            user: userInstance
        });
    })

    fastify.post('/login',  async (req, res): Promise<any> => {
        let data = await fastify.validate(req, {
            login: [new NotEmptyValidator()],
            password: [new NotEmptyValidator()],
        });
        let user = await User.findOne({login: data.login, domain: null});
        if (!user) {
            throw {
                statusCode: 404,
                text: 'auth.messages.error_wrong_login'
            }
        }
        const token = await res.jwtSign(user.getJWTPayload());
        res.setCookie('token', token, {
            path: '/',
        }).send({
            user,
        });
    })

    fastify.post('/register',  async (req, res): Promise<any> => {
        let data = await fastify.validate(req, {
            login: [new AlphanumericWithSymbolsValidator()],
            email: [new EmailValidator()],
            password: [new MinLengthValidator(8)],
        });
        let user = new User();
        user.login = data.login;
        user.email = data.email;
        user.password = await argon2.hash(data.password);

        let {publicKey, privateKey} = await generateKeys();
        user.public_key = publicKey;
        user.private_key = privateKey;
        await user.save();

        user.password = undefined;
        user.private_key = undefined;

        const token = await res.jwtSign(user.getJWTPayload());

        res.setCookie('token', token, {
            path: '/',
        }).send({
            user
        });
    })

    fastify.post('/logout',  async (req, res): Promise<any> => {
        res.clearCookie('token').send({
            status: true
        });
    })

    fastify.post('/update-profile', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let data = await fastify.validate(req, {
            about: [new MaxLengthValidator(500)],
            avatar: [new PictureValidator(false)],
        });

        let user = req.user;
        user.about = data.about;
        user.avatar = data.avatar;
        await user.save();

        res.send({
            user
        });
    })

}

module.exports = routes;
