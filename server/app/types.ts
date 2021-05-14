export interface Error {
    status: number,
    text: string
}
interface ServerConfig {
    domain: String
}
export interface ServerInstance {
    get: Function,
    post: Function,
    put: Function,
    delete: Function,
    validate: Function,
    authenticate: Function,
    authenticate_optional: Function,
    authenticate_admin: Function,
    authenticate_moderator: Function,
    config: ServerConfig,
    ws: any
}

export interface StreamSettings {
    name: String,
    description?: string;
}
