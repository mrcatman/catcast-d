import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne, JoinColumn, ManyToOne, AfterLoad,
} from 'typeorm'

import {BaseModel} from "./BaseModel";
import { User } from './User'
import { Channel } from './Channel'
import { StreamSettings } from '../types'
import { defaultStreamSettings } from '../helpers/streamSettings'
import { UserChannelPermissions } from '../helpers/permissions/list'
import { config } from '../config'

@Entity('user_permissions')
export class UserPermissions extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @ManyToOne(() => User)
    @JoinColumn({ name: 'user_id' })
    user: User;

    @ManyToOne(() => Channel)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @Column({default: false})
    confirmed: boolean;

    @Column({default: false})
    rejected: boolean;

    @Column({default: false})
    full: boolean;

    @Column({default: false})
    hidden: boolean;

    @Column({nullable: true, default: JSON.stringify([])})
    list_string: string;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @Column({nullable: true})
    comment: string;

    list: Array<string>;

    @AfterLoad()
    setComputed() {
        if (this.list_string) {
            this.list = JSON.parse(this.list_string);
        }
    }

    getActorUrl(suffix = ''): string {
        return `https://${config('server.domain')}/api/federation/channels/${this.channel.url}/permissions/${this.user.login}${suffix}`;
    }

    toObject() {
        this.setComputed();
        return {
            type: 'Note',
            from: this.channel.getActorUrl(),
            to: this.user.getActorUrl(),
            id: this.channel.getActorUrl(`/permissions/${this.user.activitypub_handle}`),
            url: this.channel.getActorUrl(`/permissions/${this.user.activitypub_handle}`),
            published: this.created_at || null,

            // custom properties
            catcastObjectType: 'UserPermissions',
            list: this.list,
            full: this.full,
            comment: this.comment || null,
            hidden: this.hidden,
        }
    }


}
