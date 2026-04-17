<template>
    <sideBar v-if="$route.name !== 'virtual-hall'"></sideBar>
    <div class="page">
        <mainHeader v-if="$route.name !== 'virtual-hall'"></mainHeader>
        <Loader v-if="StatusLoading" />
        <router-view v-else></router-view>
    </div>

    <signIn v-if="signUpModalOpen"></signIn>
    <import-budges v-if="importBudgesModalOpen"></import-budges>
    <connect2fa v-if="connect2faModalOpen"></connect2fa>
    <request-validate v-if="requestValidateModalOpen"></request-validate>
    <network-validate v-if="networkValidateModalOpen"></network-validate>
    <claim-trophy v-if="claimTrophyModal"></claim-trophy>
    <ambar-validate v-if="ambarValidateModalOpen"></ambar-validate>
    <create-your-achievement v-if="createYourAchievementModalOpen"></create-your-achievement>
    <validate-popup-open-card v-if="validatePopUpOpenCardModalOpen"></validate-popup-open-card>
    <create-achievement v-if="createAchievementModalOpen"></create-achievement>
    <trophy-room-badge v-if="trophyRoomBadgeModalOpen"></trophy-room-badge>
    <chest-show-modal v-if="chestShowModalOpen"></chest-show-modal>
    <ambar-notification v-if="notificationModalOpen"></ambar-notification>
    <network-remove-action-modal v-if="networkRemoveUnfollowModalOpen"></network-remove-action-modal>
    <delete-post v-if="deletePostModalOpen"></delete-post>
    <delete-comment v-if="deleteCommentModalOpen"></delete-comment>
    <social-proof-validate v-if="SocialProofValidateModalOpen"></social-proof-validate>
    <validate-achievement v-if="validateAchievementModalOpen"></validate-achievement>
    <revalidate-modal v-if="revalidateModalOpen"></revalidate-modal>
    <revalidate-social-proof v-if="revalidateSocialProofModalOpen"></revalidate-social-proof>
    <two-factor-auth-modal v-if="twoFactorAuthModalOpen"></two-factor-auth-modal>
    <two-factor-auth-success-modal v-if="twoFactorAuthSuccessModalOpen"></two-factor-auth-success-modal>
</template>

<script>
import mainHeader from "./components/main-header.vue";
import sideBar from "./components/sidebar.vue";
import signIn from "./components/modals/sign-in.vue";
import store from "./store/store.js";
import ImportBudges from "./components/modals/import-budges.vue";
import RequestValidate from "./components/modals/request-validate.vue";
import NetworkValidate from "./components/modals/network-validate.vue";
import ClaimTrophy from "./components/modals/claim-trophy.vue";
import AmbarValidate from "./components/modals/ambar-validate.vue";
import CreateYourAchievement from "./components/modals/create-your-achievement.vue";
import ValidatePopupOpenCard from "./components/modals/validate-popup-open-card.vue";
import CreateAchievement from "./components/modals/create-achievement.vue";
import Loader from "./components/Loader.vue";
import TrophyRoom from "./pages/TrophyRoom.vue";
import TrophyRoomBadge from "./components/modals/trophy-room-badge.vue";
import trophyRoomBadge from "./components/modals/trophy-room-badge.vue";
import ambarNotification from "./components/modals/ambar-notification.vue";
import networkRemoveActionModal from "./components/modals/network-remove-action.vue";
import DeletePost from "./components/modals/delete-post.vue";
import DeleteComment from "./components/modals/delete-comment.vue";
import SocialProofValidate from "./components/modals/social-proof-validate.vue";
import ValidateAchievement from "./components/modals/validate-achievement.vue";
import RevalidateModal from "./components/modals/revalidate-modal.vue";
import RevalidateSocialProof from "./components/modals/revalidate-social-proof.vue";
import TwoFactorAuthModal from "./components/modals/two-factor-auth-modal.vue";
import TwoFactorAuthSuccessModal from "./components/modals/two-factor-auth-success-modal.vue";
import Connect2fa from "./components/modals/connect-2fa.vue";
import ExchangeModal from "./components/modals/ExchangeModal.vue";
import ChestShowModal from "./components/modals/chest-show-modal.vue";

export default {
    components: {
        ChestShowModal,
        ExchangeModal,
        Connect2fa,
        TwoFactorAuthSuccessModal,
      TwoFactorAuthModal,
      DeleteComment,
      RevalidateSocialProof,
      RevalidateModal,
      ValidateAchievement,
      DeletePost,
        networkRemoveActionModal,
        ambarNotification,
        TrophyRoomBadge,
        TrophyRoom,
        Loader,
        CreateAchievement,
        ValidatePopupOpenCard,
        CreateYourAchievement,
        AmbarValidate,
        ClaimTrophy,
        NetworkValidate,
        RequestValidate,
        ImportBudges,
        mainHeader,
        sideBar,
        signIn,
        SocialProofValidate,
    },
    data() {
        return {}
    },
    computed: {
        trophyRoomBadge() {
            return trophyRoomBadge
        },
        StatusLoading: function () {
          return store.state.loaderStatus;
        },
        signUpModalOpen: function () {
            return store.state.signUpModalOpen;
        },
        importBudgesModalOpen: function () {
            return store.state.importBudgesModalOpen;
        },
        requestValidateModalOpen: function () {
            return store.state.requestValidateModalOpen;
        },
        networkValidateModalOpen: function () {
            return store.state.networkValidateModalOpen;
        },
        claimTrophyModal: function () {
            return store.state.modals.claimTrophyModal.show;
        },
        ambarValidateModalOpen: function () {
            return store.state.ambarValidateModalOpen;
        },
        createYourAchievementModalOpen: function () {
            return store.state.createYourAchievementModalOpen;
        },
        validatePopUpOpenCardModalOpen: function () {
            return store.state.validatePopUpOpenCardModalOpen;
        },
        createAchievementModalOpen: function () {
            return store.state.modals.createAchievement.show;
        },
        SocialProofValidateModalOpen: function () {
            return store.state.SocialProofValidateModalOpen;
        },
        trophyRoomBadgeModalOpen: function () {
            return store.state.modals.trophyRoomBadge.show;
        },
        chestShowModalOpen: function () {
            return store.state.modals.chestShowModal.show;
        },
        notificationModalOpen: function () {
            return store.state.notification.show;
        },
        networkRemoveUnfollowModalOpen: function () {
          return store.state.networkRemoveUnfollowModal.show;
        },
        deletePostModalOpen: function () {
          return store.state.deletePostModal.show;
        },
      deleteCommentModalOpen: function () {
        return store.state.deleteCommentModal.show;
      },
        validateAchievementModalOpen: function () {
        return store.state.validateAchievementModal.show;
      },
      revalidateModalOpen: function () {
        return store.state.revalidateModal.show;
      },
      revalidateSocialProofModalOpen: function () {
        return store.state.revalidateSocialProofModal.show;
      },
      twoFactorAuthModalOpen: function () {
        return store.state.modals.twoFactorAuthModal.show;
      },
      twoFactorAuthSuccessModalOpen: function () {
        return store.state.modals.twoFactorAuthModalSuccess.show;
      },
      connect2faModalOpen: function () {
        return store.state.modals.connect2faModalOpen.show;
      }

    },
    methods: {
        setLoading(isLoading) {
            this.isLoading = isLoading;
        },
    }
}
</script>

<style>

</style>
