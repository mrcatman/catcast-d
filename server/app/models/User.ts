import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    OneToOne, JoinColumn
} from "typeorm";
import {Picture} from "./Picture";
import {BaseModel} from "./BaseModel";
import * as config from '../config';

@Entity('users')
export class User extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column()
    login: string;

    @Column()
    email: string;

    @Column({ select: false })
    password: string;

    @Column({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP'})
    last_time_seen: Date;

    @OneToOne(() => Picture, {
        eager: true
    })
    @JoinColumn()
    avatar: Picture;

    @Column({nullable: true})
    about: string;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    @Column({nullable: true})
    domain: string; // Federation domain; if null, channel is on the current server

    getJWTPayload() {
        return {
            id: this.id,
            login: this.login
        }
    }

    getWebUrl(): string {
        return `https://${config.domain}/users/${this.login}`;
    }

    getActorUrl(suffix: string = ''): string {
        return `https://${config.domain}/api/federation/users/${this.login}${suffix}`;
    }

    @Column({type: 'text'})
    public_key: string;

    @Column({type: 'text', select: false })
    private_key: string;

}
