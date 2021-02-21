import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    JoinColumn,
    ManyToOne, AfterLoad,
} from 'typeorm'
import { User } from "./User";
import {BaseModel} from "./BaseModel";
import {Channel} from "./Channel";

@Entity('streams')
export class Stream extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    name: string;

    @Column({nullable: true})
    description: string;

    @Column({nullable: true})
    watch_url: string;

    @Column({nullable: true})
    cover_url: string;

    @Column({nullable: true})
    object_id: string;

    @CreateDateColumn({ type: 'timestamp' })
    started_at: Date;

    @Column({ type: 'timestamp', nullable: true })
    ended_at: Date;

    @Column({ nullable: true })
    channel_id: number;
    @ManyToOne(() => Channel, channel => channel.streams)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @ManyToOne(() => User, user => user.streams)
    @JoinColumn({ name: 'broadcaster_id' })
    broadcaster: User;

    toActivity(type: string) {
        return {
            actor: this.channel.getActorUrl(),
            cc: [this.channel.getActorUrl('/followers')], // now public only yet,
            id: this.channel.getActorUrl(`/streams/${this.id}/activity`),
            object: this.toObject(),
            to: ['https://www.w3.org/ns/activitystreams#Public'],
            type
        }
    }

    toObject() {
        return {
            type: 'Note',
            id: this.channel.getActorUrl(`/streams/${this.id}`),
            url: this.channel.getActorUrl(`/streams/${this.id}`),
            name: this.name,
            content: this.name + `\n \n ${this.channel.getWebUrl()}`,
            published: this.started_at,

            // custom properties
            catcastObjectType: 'Stream',
            watchUrl: this.watch_url,
            coverUrl: this.cover_url,
            broadcaster: this.broadcaster.getActorUrl(),
            channel: this.channel.getActorUrl(),
            endedAt: this.ended_at
        }
    }

    @AfterLoad()
    setComputed() {
        if (!this.object_id) {
            let domain = 'http://localhost:8100/'; //todo: change
            if (!this.watch_url) {
                this.watch_url = domain + 'hls/' + this.channel_id + '/index.m3u8';
            }
            if (!this.cover_url) {
                this.cover_url = domain + 'screens/' + this.channel_id + '.png';
            }
        }
    }

}
