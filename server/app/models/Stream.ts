import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    JoinColumn,
    ManyToOne,
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
    object_id: string;

    @CreateDateColumn({ type: 'timestamp' })
    started_at: Date;

    @Column({ type: 'timestamp', nullable: true })
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
            catcastObjectType: 'Stream',
            broadcaster: this.broadcaster.getActorUrl(),
            channel: this.channel.getActorUrl(),
            endedAt: this.ended_at
        }
    }

}
