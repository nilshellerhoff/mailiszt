import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";
import EmptyRouterView from "@/views/EmptyRouterView.vue";
import Login from '@/views/Login.vue'
import Settings from '@/views/Settings.vue'

import AddMember from '@/views/members/AddMember.vue'
import Members from '@/views/members/Members.vue'
import EditMember from '@/views/members/EditMember.vue'

import AddGroup from '@/views/groups/AddGroup.vue'
import Groups from '@/views/groups/Groups.vue'
import EditGroup from '@/views/groups/EditGroup.vue'

import AddMailbox from '@/views/mailboxes/AddMailbox.vue'
import Mailboxes from '@/views/mailboxes/Mailboxes.vue'
import EditMailbox from '@/views/mailboxes/EditMailbox.vue'

import Mails from '@/views/mails/Mails.vue'
import ViewMail from '@/views/mails/ViewMail.vue'

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
  {
    path: "/settings",
    name: "Settings",
    component: Settings,
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
        name: "Members",
        components: {
          default:  Members,
          dialog: EditMember,
        }
      },
      {
        path: "add",
        name: "Members",
        components: {
          default: Members,
          dialog: AddMember,
        }
      },
    ],
  },
  // inactive members
  {
    path: "/members/inactive",
    component: EmptyRouterView,
    children: [
      {
        name: 'Inactive members',
        path: '',
        component: Members,
        props: { active: false }
      },
      {
        path: "edit/:id",
        name: "Inactive members",
        components: {
          default:  Members,
          dialog: EditMember,
        },
        props: {
          default: { active: false },
          dialog: { active: false }
        }
      },
      {
        path: "add",
        name: "Inactive members",
        components: {
          default: Members,
          dialog: AddMember,
        },
        props: {
          default: { active: false },
          dialog: { active: false }
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
        name: "Groups",
        components: {
          default:  Groups,
          dialog: EditGroup,
        }
      },
      {
        path: "add",
        name: "Groups",
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
        name: "Mailboxes",
        components: {
          default:  Mailboxes,
          dialog: EditMailbox,
        }
      },
      {
        path: "add",
        name: "Mailboxes",
        components: {
          default: Mailboxes,
          dialog: AddMailbox,
        }
      },
    ],
  },
  // mails
  {
    path: "/mails",
    component: EmptyRouterView,
    children: [
      {
        name: 'Mails',
        path: '',
        component: Mails,
      },
      {
        path: ":id",
        name: "Mails",
        components: {
          default:  Mails,
          dialog: ViewMail,
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
