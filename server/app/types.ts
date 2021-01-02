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
    config: ServerConfig
}
