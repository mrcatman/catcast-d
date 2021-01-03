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

import * as config from '../config';

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
    @JoinColumn({ name: 'channel_id' })
    owner: User;

    @OneToOne(() => Picture, { eager: true })
    @JoinColumn()
    logo: Picture;

    @OneToMany(() => Stream, stream => stream.channel)
    streams: Stream[];

    @Column({nullable: true})
    domain: string; // Federation domain; if null, channel is on the current server

    screenshot: string;

    live_url: string;

    @AfterLoad()
    setComputed() {
        let domain = 'http://localhost:8080/'; //todo: change
        if (this.is_online) {
            this.screenshot = domain + 'screens/' + this.id + '.png';
            this.live_url = domain + 'hls/' + this.id + '/index.m3u8';
        }
    }

    getWebUrl(): string {
        return `https://${config.domain}/${this.url}`;
    }

    getActorUrl(suffix = ''): string {
        return `https://${config.domain}/api/federation/channels/${this.url}${suffix}`;
    }

    @Column({type: 'text'})
    public_key: string;

    @Column({type: 'text', select: false })
    private_key: string;

}
