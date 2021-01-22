import { MoreThan } from 'typeorm'
import { format } from "date-fns";

export const MoreThanDate = (ts: number) => MoreThan(format(new Date(ts), 'yyyy-mm-dd HH:MM:ss'))
