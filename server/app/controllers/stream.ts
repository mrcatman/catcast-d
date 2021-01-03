import {ServerInstance} from "../types";
import {StreamKey} from "../models/StreamKey";
import getChannelFromNginxRequest from "../helpers/getChannelFromNginxRequest";
import {Channel} from "../models/Channel";
import {checkRightsOrFail} from "../helpers/checkRights";
import {Stream} from "../models/Stream";

async function routes (fastify: ServerInstance, options) {

    fastify.get('/key/:id', {
        preValidation: [fastify.authenticate]
    }, async (req, res) => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        await checkRightsOrFail(req.user, channel, ['broadcast']);
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

            let recentlyEndedStream = await Stream.findOne({
                channel: {
                    id: channel.id
                },
                broadcaster: {
                    id: user.id
                }
            })

            let stream = new Stream();
            stream.fill({
                name: `${channel.name} - broadcast ${new Date().toLocaleString()}`, // todo: generate stream name from channel settings,
                started_at: new Date(),
                channel: channel,
                broadcaster: user,
            })
            await stream.save();

            channel.current_stream = stream;
            await channel.save();

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
                stream.ended_at = new Date();
                await stream.save();
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
