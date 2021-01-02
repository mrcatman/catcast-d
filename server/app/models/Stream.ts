import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne,
    JoinColumn, AfterLoad,
} from "typeorm";
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

    @OneToOne(() => Channel)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @OneToOne(() => User)
    @JoinColumn({ name: 'broadcaster_id' })
    broadcaster: User;


}
