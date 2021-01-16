import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne, JoinColumn, OneToMany,
} from 'typeorm'
import {Picture} from "./Picture";
import {BaseModel} from "./BaseModel";
import { Stream } from './Stream'
import { Channel } from './Channel'

import { getConfig } from '../helpers/getConfig'
import { SHARED_INBOX_URL } from '../federation/constants'
import { Follower } from './Follower'
const config = getConfig();

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
    about: string;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    @Column({nullable: true})
    domain: string; // Federation domain; if null, user is on the current server

    @OneToMany(() => Channel, channel => channel.owner)
    owned_channels: Channel[];

    @OneToMany(() => Stream, stream => stream.broadcaster)
    streams: Channel[];

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

    getJWTPayload() {
        return {
            id: this.id,
            login: this.login
        }
    }

    getWebUrl(): string {
        return `https://${config.domain}/users/${this.login}`;
    }

    getActorUrl(suffix: string = ''): string {
        return `https://${this.domain || config.domain}/api/federation/users/${this.login}${suffix}`;
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
            preferredUsername: this.login,
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
                url: this.avatar? this.avatar.full_url : `https://${config.domain}/static/no-logo.png`
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

}
