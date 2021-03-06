import {ServerInstance} from "../types";
import {StreamKey} from "../models/StreamKey";
import getChannelFromNginxRequest from "../helpers/getChannelFromNginxRequest";
import {Channel} from "../models/Channel";
import {Stream} from "../models/Stream";
import { Create, Update } from '../federation/activities/Create'
import { MoreThanDate } from '../helpers/dates'
import { UserChannelPermissions } from '../helpers/permissions/list'
import {checkPermissionsOrFail} from "../helpers/permissions/check";

async function routes (fastify: ServerInstance, options) {

    fastify.get('/key/:id', {
        preValidation: [fastify.authenticate]
    }, async (req, res) => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        await checkPermissionsOrFail(req.user, channel, [UserChannelPermissions.BROADCAST]);
        let streamKey = await StreamKey.findOne(
            {
                user: {
                    id: req.user.id
                },
                channel: {
                    id: channel.id
                }
            }
        );
        if (req.query.new && streamKey) {
            await streamKey.remove();
            streamKey = null;
        }
        if (!streamKey) {
            streamKey = new StreamKey()
            streamKey.fill({
                user: req.user,
                channel,
                key: StreamKey.generateKey()
            })
            await streamKey.save();
            streamKey.channel = undefined;
            streamKey.user = undefined;
        }
        streamKey.setComputed();
        res.send({key: streamKey});
    });

    fastify.get('/servers',  async (req, res): Promise<any> => {
       let servers = ['rtmp://localhost:1935/live'];
       res.send({
            servers
       });
    });

    fastify.post('/auth',  async (req, res): Promise<any> => {
        try {
            let [channel, user] = await getChannelFromNginxRequest(req);
            channel.is_online = true;

            let stream = await Stream.findOne({
                where: {
                    channel: {
                        id: channel.id
                    },
                    broadcaster: {
                        id: user.id
                    },
                    ended_at: MoreThanDate(new Date().getTime() - 1000 * 120),
                },
                relations: ['channel', 'broadcaster']
            })
            if (stream) { // if found recently ended stream, delete end date
                stream.ended_at = null;
                await stream.save();
                Update(stream);
            } else {
                stream = new Stream();
                stream.fill({
                    name: channel.stream_settings && channel.stream_settings.name ? channel.stream_settings.name : `${channel.name} - broadcast ${new Date().toLocaleString()}`, // todo: generate stream name from channel settings,
                    description: channel.stream_settings && channel.stream_settings.description ? channel.stream_settings.description : '',
                    started_at: new Date(),
                    channel: channel,
                    broadcaster: user,
                })
                await stream.save();
                channel.current_stream = stream;
                await channel.save();
                Create(stream);
            }



            res.send({
                status: true
            });
        } catch (e) {
            console.log(e);
            res.code(403).send({
                status: false
            })
        }
    })

    fastify.post('/end',  async (req, res): Promise<any> => {
        try {
            let [channel] = await getChannelFromNginxRequest(req);
            channel.is_online = false;

            await channel.save();

            let stream = channel.current_stream;
            if (stream) {
                stream = await Stream.findOne({
                    where: {
                        id: stream.id,
                    },
                    relations: ['channel', 'broadcaster']
                })
                stream.ended_at = new Date();
                await stream.save();
                Update(stream);
            }
            res.send({
                status: true
            });
        } catch (e) {
            res.code(403).send({
                status: false
            })
        }
    })

}

module.exports = routes;
