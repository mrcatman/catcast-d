import {ServerInstance} from "../types";
import {Like} from "typeorm";
import { User } from '../models/User'

async function routes (fastify: ServerInstance, options) {

  fastify.get('/search', async (req, res) => {
      let query = req.query.q || "";
      let users = await User.paginate({
          where: [
              {name: Like(`%${query}%`)},
              {login: Like(`%${query}%`)}
          ]
      }, req);
      res.send(users);
  })


}

module.exports = routes;
