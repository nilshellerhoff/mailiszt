import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";
import EmptyRouterView from "@/views/EmptyRouterView.vue";

import AddMember from '@/views/members/AddMember.vue'
import Members from '@/views/members/Members.vue'
import EditMember from '@/views/members/EditMember.vue'

import AddGroup from '@/views/groups/AddGroup.vue'
import Groups from '@/views/groups/Groups.vue'
import EditGroup from '@/views/groups/EditGroup.vue'

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  // members
  {
    path: "/members",
    component: EmptyRouterView,
    children: [
      {
        name: 'Members',
        path: '',
        component: Members,
      },
      {
        path: "edit/:id",
        name: "Members.Edit",
        components: {
          default:  Members,
          dialog: EditMember,
        }
      },
      {
        path: "add",
        name: "Members.Add",
        components: {
          default: Members,
          dialog: AddMember,
        }
      },
    ],
  },
  // groups
  {
    path: "/groups",
    component: EmptyRouterView,
    children: [
      {
        name: 'Groups',
        path: '',
        component: Groups,
      },
      {
        path: "edit/:id",
        name: "Groups.Edit",
        components: {
          default:  Groups,
          dialog: EditGroup,
        }
      },
      {
        path: "add",
        name: "Groups.Add",
        components: {
          default: Groups,
          dialog: AddGroup,
        }
      },
    ],
  },
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes,
});

export default router;
