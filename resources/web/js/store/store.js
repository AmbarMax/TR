import { createStore } from "vuex";
import {contractAddress, contractAddressKey} from "../config.js";
import { contractABI } from "../config.js";
import { contractABIKey } from "../config.js";
import Web3 from "web3";

const store = createStore({
    state () {
        return {
            signUpModalOpen: false,
            authorized: false,
            userName: null,
            userEmail: null,
            userAvatar: null,
            userBackground: null,
            userUsername: null,
            userGoogle2fa_status: false,
            importBudgesModalOpen: false,
            requestValidateModalOpen: false,
            networkValidateModalOpen: false,
            ambarValidateModalOpen: false,
            exchangeModalOpen: false,
            validatePopUpOpenCardModalOpen: false,
            createAchievementModalOpen: false,
            networkRemoveUnfollowModalOpen: false,
            // createYourAchievementModalOpen: false,
            showTestData: false,
            loaderStatus: false,
            activeSideBar: false,
            updateHeaderData: false,
            getDataOnValidationPage: false,
            SocialProofValidateModalOpen: false,
            totalFollowers: 0,
            followers: [],
            totalFollowing: 0,
            followings: [],
            followingsCurrentPage: 1,
            myPosts: [],
            myPostsTotal: 0,
            posts: [],
            postsTotal: 0,
            achievements: [],
            modals: {
                trophyRoomBadge: {
                    show: false,
                    data: {
                        imageUrl: null,
                        name: null,
                        description: null
                    }
                },
                claimTrophyModal: {
                    show: false,
                    data: null
                },
                createAchievement: {
                    show: false,
                    data: null
                },
                twoFactorAuthModal: {
                    show: false,
                    data: null
                },
                twoFactorAuthModalSuccess: {
                    show: false,
                    data: null
                },
                connect2faModalOpen: {
                    show: false,
                    data: null
                },
                chestShowModal: {
                    show: false,
                    data: []
                },
            },
            notification: {
                show: false,
                type: '',
                message: ''
            },
            messageNotification: {
                show: false,
                data: []
            },
            comments: [],
            networkRemoveUnfollowModal: {
                show: false,
                title: '',
                btn_text: '',
                action: '',
                user_id: ''
            },
            deletePostModal: {
                show: false,
                title: '',
                btn_text: '',
                post_id: ''
            },
            deleteCommentModal: {
                show: false,
                title: '',
                btn_text: '',
                commentId: '',
                postId: ''
            },
            validateAchievementModal: {
                show: false,
                title: '',
                entity_id: ''
            },
            revalidateModal: {
                show: false,
                title: '',
                entity_id: ''
            },
            revalidateSocialProofModal: {
                show: false,
                title: '',
                entity_id: ''
            },
            revalidateAch: null,
            unread_notifications_count: 0,
            user: {
                balances: {
                    uru: 0,
                    ambar: 0,
                    rune: 0
                },
                roles: []
            },
            refreshTokenPromise: null,
            contractAddress: contractAddress,
            contractAddressKey: contractAddressKey,
            contractABI: contractABI,
            contractABIKey: contractABIKey,
            isLoading: false,
            web3: null,
            userAddress: '',
            contract: null,
        }
    },
    getters: {
        isLoggedIn: (state) => !!state.authorized,
        isPlayer:   (state) => state.user?.account_type === 'player',
        isBrand:    (state) => state.user?.account_type === 'brand',

        // Parameterized getters — return a function. Use in components as
        // `$store.getters.can('brand.manage_hall')` or mapGetters + the
        // same syntax. Null-safe against a user that has not been hydrated.
        can: (state) => (permission) =>
            Array.isArray(state.user?.permissions) &&
            state.user.permissions.includes(permission),
        hasRole: (state) => (role) =>
            Array.isArray(state.user?.roles) &&
            state.user.roles.includes(role),

        // Role shortcuts — cheaper in template than calling hasRole()
        // with a literal argument everywhere.
        isMember:     (state, getters) => getters.hasRole('member'),
        isBrandOwner: (state, getters) => getters.hasRole('brand_admin'),
        isModerator:  (state, getters) => getters.hasRole('tr_moderator'),
        isAdmin:      (state, getters) => getters.hasRole('tr_admin') || getters.hasRole('tr_superadmin'),
        isSuperAdmin: (state, getters) => getters.hasRole('tr_superadmin'),
        isStaff:      (state, getters) => getters.isModerator || getters.isAdmin,
    },
    mutations: {
        updateHeaderData () {
            store.state.updateHeaderData = !store.state.updateHeaderData;
        },
        updateDataOnValidationPage () {
            store.state.getDataOnValidationPage = !store.state.getDataOnValidationPage;
        },
        setRefreshTokenPromise(state, promise) {
            state.refreshTokenPromise = promise;
        },
        setWeb3(state, web3) {
            state.web3 = web3;
        },
        setUserAddress(state, address) {
            state.userAddress = address;
        },
        setLoading(state, isLoading) {
            state.isLoading = isLoading;
        },
    },
    actions: {
        async initMetaMaskConnection({commit},payload) {
            let nftType = 'trophy';
            nftType = payload?.nftType ? payload.nftType : nftType;
            if (window.ethereum) {
                store.state.web3 = new Web3(window.ethereum);
                try {
                    await window.ethereum.request({method: 'eth_requestAccounts'}); // Request account access
                    const accounts = await store.state.web3.eth.getAccounts();
                    store.state.userAddress = accounts[0];
                    this.dispatch('initializeContract', {nftType});
                } catch (error) {
                    console.error("User denied account access or an error occurred:", error);
                    store.state.notification = {
                        message: 'Metamask connection is required!',
                        type: 'warning',
                        show: true
                    }
                }
            } else {
                console.log("Non-Ethereum browser detected. You should consider trying MetaMask!");
                store.state.notification = {
                    message: 'Non-Ethereum browser detected. You should consider trying MetaMask!',
                    type: 'warning',
                    show: true
                }
            }
        },
        async initializeContract({commit},payload) {
            let nftType = 'trophy';
            nftType = payload?.nftType ? payload.nftType : nftType;
            let contractAddress;
            let contractABI;
            if (nftType === 'trophy') {
                contractABI = store.state.contractABI;
                contractAddress =  store.state.contractAddress;
            } else if (nftType === 'key') {
                contractABI = store.state.contractABIKey;
                contractAddress = store.state.contractAddressKey;
            }

            store.state.contract = new store.state.web3.eth.Contract(contractABI, contractAddress);
        },
    }
});

export default store;
