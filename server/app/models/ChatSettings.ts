
import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    OneToOne,
    JoinColumn,
} from 'typeorm'
import { Channel } from "./Channel";
import { BaseModel } from "./BaseModel";

@Entity('chat_settings')
export class ChatSettings extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @OneToOne(type => Channel)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @Column()
    channel_id: number;



    @Column({default: false})
    disabled: boolean;

    @Column({default: ''})
    motd: string;


}
