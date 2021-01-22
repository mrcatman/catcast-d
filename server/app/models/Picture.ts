import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    AfterLoad
} from "typeorm";
import {BaseModel} from "./BaseModel";
import { config } from '../config'


@Entity('pictures')
export class Picture extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column({nullable: true})
    remote_url: string | null;

    @Column({nullable: true})
    path: string;

    @Column({nullable: true})
    user_id: number;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    full_url: string;

    @AfterLoad()
    setComputed() {
        let url = this.remote_url ? this.remote_url : `https://${config('server.domain')}${this.path}`;
        if (url.indexOf('https://localhost') !== -1) {
            url = url.replace('https://localhost', 'http://localhost');
        }
        this.full_url = url;
    }


}
