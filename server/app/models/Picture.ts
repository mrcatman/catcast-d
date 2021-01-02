import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    CreateDateColumn,
    UpdateDateColumn,
    AfterLoad
} from "typeorm";
import {BaseModel} from "./BaseModel";

@Entity('pictures')
export class Picture extends BaseModel {

    @PrimaryGeneratedColumn()
    id: number;

    @Column({nullable: true})
    domain: string | null;

    @Column()
    path: string;

    @Column()
    user_id: number;

    @CreateDateColumn({ type: 'timestamp' })
    created_at: Date;

    @UpdateDateColumn({ type: 'timestamp' })
    updated_at: Date;

    full_url: string;

    @AfterLoad()
    setComputed() {
        let domain = this.domain || 'http://localhost:4002';
        this.full_url = domain + this.path;
    }


}
