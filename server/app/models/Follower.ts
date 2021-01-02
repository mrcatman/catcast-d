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

}
