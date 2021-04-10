
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

@Entity('chat_messages')
export class ChatMessage extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    content: string;

    @ManyToOne(type => User)
    @JoinColumn({ name: 'author_id' })
    author: User;

    @ManyToOne(type => Channel)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @Column()
    author_id: number;

    @Column()
    channel_id: number;

    @Column({ type: 'datetime', nullable: true })
    created_at: Date;

}
