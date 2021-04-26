import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne,
    JoinColumn, AfterLoad, OneToMany, ManyToOne, BeforeInsert, BeforeUpdate,
} from 'typeorm'
import { User } from "./User";
import {Picture} from "./Picture";
import {Stream} from "./Stream";
import {BaseModel} from "./BaseModel";

import { config } from '../config'

import { SHARED_INBOX_URL } from '../federation/constants'
import { Follower } from './Follower'

import { defaultStreamSettings } from '../helpers/streamSettings'
import { StreamSettings } from '../types'

@Entity('channels')
export class Channel extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    url: string;

    @Column()
    name: string;

    @Column({ nullable: true })
    description: string;

    @Column({default: false})
    is_online: boolean;

    @OneToOne(() => Stream)
    @JoinColumn({ name: 'current_stream_id' })
    current_stream: Stream;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    @ManyToOne(() => User, user => user.owned_channels)
    @JoinColumn({ name: 'owner_id' })
    owner: User;

    @OneToOne(() => Picture, { eager: true })
    @JoinColumn()
    logo: Picture;

    @OneToMany(() => Stream, stream => stream.channel)
    streams: Stream[];

    @Column({nullable: true})
    domain: string; // Federation domain; if null, channel is on the current server

    @Column({ nullable: true })
    followers_count: number; // Followers count for remote channels

    @Column({nullable: true})
    actor_id: string;

    @Column({nullable: true})
    inbox_url: string;

    @Column({nullable: true})
    outbox_url: string;

    @Column({nullable: true})
    shared_inbox_url: string;

    @Column({nullable: true})
    web_url: string;

    screenshot: string;

    live_url: string;

    @Column({nullable: true, default: JSON.stringify(defaultStreamSettings)})
    stream_settings_value: string;

    stream_settings: StreamSettings;

    activitypub_handle: String;

    @AfterLoad()
    setComputed() {
        this.activitypub_handle = `${this.url}@${this.domain || config('server.domain')}`;
        if (this.current_stream) {
            this.current_stream.channel = undefined; // prevent circular JSON
        }
        if (this.stream_settings_value) {
            this.stream_settings = JSON.parse(this.stream_settings_value) as StreamSettings;
        }
    }


    @Column({type: 'text'})
    public_key: string;

    @Column({type: 'text', select: false, nullable: true })
    private_key: string;

    getWebUrl(): string {
        return `https://${this.domain || config('server.domain')}/${this.url}`;
    }

    getActorUrl(suffix = ''): string {
        return `https://${this.domain || config('server.domain')}/api/federation/channels/${this.url}${suffix}`;
    }

    toActivity(type: string) {
        return {
            actor: this.getActorUrl(),
            cc: [this.getActorUrl('/followers')],
            id: this.getActorUrl(),
            object: this.toObject(),
            to: ['https://www.w3.org/ns/activitystreams#Public'],
            type
        }
    }

    toObject() {
        return {
            id: this.getActorUrl(),
            actor: this.owner ? this.owner.getActorUrl() : undefined,
            type: 'Group', // maybe change to something better?
            catcastActorType: 'Channel',
            following: this.getActorUrl('/following'),
            followers: this.getActorUrl('/followers'),
            inbox: this.getActorUrl('/inbox'),
            outbox: this.getActorUrl('/outbox'),
            preferredUsername: this.name,
            name: this.url,
            summary: this.description,
            url: this.getWebUrl(),
            publicKey: {
                id: this.getActorUrl() + '#key',
                owner: this.getActorUrl(),
                publicKeyPem: this.public_key
            },
            icon: {
                type: 'Image',
                mediaType: 'image/png',
                url: this.logo? this.logo.full_url : `https://${config('server.domain')}/static/no-logo.png`
            },
            endpoints: {
                sharedInbox: SHARED_INBOX_URL
            }
        }
    }

    async followersCount() : Promise<number> {
        return await Follower.count({
            actor_id: this.id,
            actor_type: Follower.TYPE_CHANNEL
        })
    }

}
