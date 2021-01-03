import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne, JoinColumn
} from "typeorm";

import {BaseModel} from "./BaseModel";
import { User } from './User'
import { Channel } from './Channel'

@Entity('followers')
export class Follower extends BaseModel {

    public static readonly TYPE_CHANNEL = 'channel';
    public static readonly TYPE_USER = 'user';

    @PrimaryGeneratedColumn()
    id: number;

    @OneToOne(() => User)
    @JoinColumn({ name: 'follower_id' })
    follower: User;

    @Column()
    actor_id: number;

    @Column()
    actor_type: string;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    actor: Channel | User;

    async loadActor(): Promise<Channel | User> {
        let entityType = this.actor_type === 'channel' ? Channel : User;
        this.actor = await entityType.findOne({
            id: this.actor_id
        });
        return this.actor;
    }

}
