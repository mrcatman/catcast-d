const crypto = require("crypto");
import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    OneToOne,
    JoinColumn, AfterLoad, ManyToOne,
} from 'typeorm'
import { User } from "./User";
import { Channel } from "./Channel";
import { BaseModel } from "./BaseModel";



@Entity('stream_keys')
export class StreamKey extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    key: string;

    @OneToOne(type => User)
    @JoinColumn({ name: 'user_id' })
    user: User;

    @ManyToOne(type => Channel)
    @JoinColumn({ name: 'channel_id' })
    channel: Channel;

    @Column()
    channel_id: number;


    static generateKey() {
        return crypto.randomBytes(12).toString('hex');
    }

    full_key: string;

    @AfterLoad()
    setComputed() {
        this.full_key = this.channel_id + '?key=' + this.key;
    }


}
