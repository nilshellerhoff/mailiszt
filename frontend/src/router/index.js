import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";
import EmptyRouterView from "@/views/EmptyRouterView.vue";
import Login from '@/views/Login.vue'

import AddMember from '@/views/members/AddMember.vue'
import Members from '@/views/members/Members.vue'
import EditMember from '@/views/members/EditMember.vue'

import AddGroup from '@/views/groups/AddGroup.vue'
import Groups from '@/views/groups/Groups.vue'
import EditGroup from '@/views/groups/EditGroup.vue'

import AddMailbox from '@/views/mailboxes/AddMailbox.vue'
import Mailboxes from '@/views/mailboxes/Mailboxes.vue'
import EditMailbox from '@/views/mailboxes/EditMailbox.vue'

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
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
  // mailboxes
  {
    path: "/mailboxes",
    component: EmptyRouterView,
    children: [
      {
        name: 'Mailboxes',
        path: '',
        component: Mailboxes,
      },
      {
        path: "edit/:id",
        name: "Mailboxes.Edit",
        components: {
          default:  Mailboxes,
          dialog: EditMailbox,
        }
      },
      {
        path: "add",
        name: "Mailboxes.Add",
        components: {
          default: Mailboxes,
          dialog: AddMailbox,
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
