import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne, JoinColumn, OneToMany, AfterLoad,
} from 'typeorm'
import {Picture} from "./Picture";
import {BaseModel} from "./BaseModel";
import { Stream } from './Stream'
import { Channel } from './Channel'

import { config } from '../config'
import { SHARED_INBOX_URL } from '../federation/constants'
import { Follower } from './Follower'

import { Role } from '../helpers/roles'
import { StreamSettings } from '../types'

@Entity('users')
export class User extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    login: string;

    @Column({nullable: true})
    email: string;

    @Column({ select: false, nullable: true })
    password: string;

    @Column({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP'})
    last_time_seen: Date;

    @OneToOne(() => Picture, {
        eager: true
    })
    @JoinColumn()
    avatar: Picture;

    @Column({nullable: true})
    name: string;

    @Column({nullable: true})
    about: string;

    @Column({default: 0})
    role_id: number;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    @Column({nullable: true})
    domain: string; // Federation domain; if null, user is on the current server

    @OneToMany(() => Channel, channel => channel.owner)
    owned_channels: Channel[];

    @OneToMany(() => Stream, stream => stream.broadcaster)
    streams: Stream[];

    @Column({type: 'text'})
    public_key: string;

    @Column({type: 'text', select: false, nullable: true })
    private_key: string;

    @Column({ nullable: true })
    followers_count: number; // Followers count for remote users

    @Column({nullable: true})
    actor_id: string;

    @Column({nullable: true})
    inbox_url: string;

    @Column({nullable: true})
    outbox_url: string;

    @Column({nullable: true})
    shared_inbox_url: string;

    @Column({nullable: true})
    key_id: string;

    @Column({nullable: true})
    web_url: string;


    getJWTPayload() {
        return {
            id: this.id,
            login: this.login
        }
    }

    getWebUrl(): string {
        return `https://${config('server.domain')}/users/${this.login}`;
    }

    getActorUrl(suffix: string = ''): string {
        return `https://${this.domain || config('server.domain')}/api/federation/users/${this.login}${suffix}`;
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
            type: 'Person',
            catcastActorType: 'User',
            following: this.getActorUrl('/following'),
            followers: this.getActorUrl('/followers'),
            inbox: this.getActorUrl('/inbox'),
            outbox: this.getActorUrl('/outbox'),
            preferredUsername: this.name || this.login,
            name: this.login,
            summary: this.about,
            url: this.getWebUrl(),
            publicKey: {
                id: this.getActorUrl() + '#key',
                owner: this.getActorUrl(),
                publicKeyPem: this.public_key
            },
            icon: {
                type: 'Image',
                mediaType: 'image/png',
                url: this.avatar? this.avatar.full_url : `https://${config('server.domain')}/static/no-logo.png`
            },
            endpoints: {
                sharedInbox: SHARED_INBOX_URL
            }
        }
    }

    async followersCount() : Promise<number> {
        return await Follower.count({
            actor_id: this.id,
            actor_type: Follower.TYPE_USER
        })
    }

    isAdmin() : boolean {
        return this.role_id === Role.ADMIN;
    }

    activitypub_handle: String;

    @AfterLoad()
    setComputed() {
        this.activitypub_handle = `${this.login}@${this.domain || config('server.domain')}`;
    }

}
