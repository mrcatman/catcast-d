
import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    OneToOne,
    JoinColumn,
    ManyToOne,
} from 'typeorm'
import { User } from "./User";
import { Channel } from "./Channel";
import { BaseModel } from "./BaseModel";

@Entity('user_bans')
export class UserBan extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @ManyToOne(type => User)
    @JoinColumn({ name: 'user_id' })
    user: User;

    @ManyToOne(type => User)
    @JoinColumn({ name: 'blocked_by_id' })
    blocked_by: User;

    @ManyToOne(type => Channel)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @Column({ type: 'datetime', nullable: true })
    created_at: Date;

    @Column()
    user_id: number;

    @Column({nullable: true})
    blocked_by_id: number;

    @Column()
    channel_id: number;

    @Column({ nullable: true })
    comment: string;

}
