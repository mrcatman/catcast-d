import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne,
    JoinColumn, AfterLoad, ManyToOne,
} from 'typeorm'
import { User } from "./User";
import {Picture} from "./Picture";
import {BaseModel} from "./BaseModel";

import * as config from '../config';
import {Channel} from "./Channel";

@Entity('streams')
export class Stream extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    name: string;

    @CreateDateColumn({ type: 'timestamp' })
    started_at: Date;

    @UpdateDateColumn({ type: 'timestamp', nullable: true })
    ended_at: Date;

    @ManyToOne(() => Channel, channel => channel.streams)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @ManyToOne(() => User, user => user.streams)
    @JoinColumn({ name: 'broadcaster_id' })
    broadcaster: User;

    toCreateActivity() {
        return {
            actor: this.channel.getActorUrl(),
            cc: [this.channel.getActorUrl('/followers')], // now public only yet,
            id: this.channel.getActorUrl(`/streams/${this.id}/activity`),
            object: this.toObject(),
            to: ['https://www.w3.org/ns/activitystreams#Public'],
            type: 'Create'
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
            catcast_object_type: 'Stream',
            broadcaster: this.broadcaster.getActorUrl(),
            channel: this.channel.getActorUrl(),
            ended_at: this.ended_at
        }
    }

}
