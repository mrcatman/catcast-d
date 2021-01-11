import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne,
    JoinColumn, AfterLoad, OneToMany, ManyToOne,
} from 'typeorm'
import { User } from "./User";
import {Picture} from "./Picture";
import {Stream} from "./Stream";
import {BaseModel} from "./BaseModel";

import { getConfig } from '../helpers/getConfig'
import { SHARED_INBOX_URL } from '../federation/constants'
const config = getConfig();

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
    inbox_url: string;

    @Column({nullable: true})
    outbox_url: string;

    @Column({nullable: true})
    shared_inbox_url: string;

    @Column({nullable: true})
    web_url: string;

    screenshot: string;

    live_url: string;

    @AfterLoad()
    setComputed() {
        let domain = 'http://localhost:8080/'; //todo: change
        if (this.is_online) {
            this.screenshot = domain + 'screens/' + this.id + '.png';
            this.live_url = domain + 'hls/' + this.id + '/index.m3u8';
        }
        if (this.current_stream) {
            this.current_stream.channel = undefined; // prevent circular JSON
        }
    }

    @Column({type: 'text'})
    public_key: string;

    @Column({type: 'text', select: false, nullable: true })
    private_key: string;

    getWebUrl(): string {
        return `https://${this.domain || config.domain}/${this.url}`;
    }

    getActorUrl(suffix = ''): string {
        return `https://${this.domain || config.domain}/api/federation/channels/${this.url}${suffix}`;
    }

    toObject() {
        return {
            id: this.getActorUrl(),
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
                url: this.logo? this.logo.full_url : `https://${config.domain}/static/no-logo.png`
            },
            endpoints: {
                sharedInbox: SHARED_INBOX_URL
            }
        }
    }

}
