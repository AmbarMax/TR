<template>
    <div v-if="showModal && currentStep === 1" class="modal_background" @click.self="closeClaimTrophy">
        <Loader v-if="statusLoading"/>
        <div v-else class="modal_window modal_forge_trophy mb-10">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeClaimTrophy"
                 class="modal_close_button">
            <h1 class="modal_header">
                Claim your trophy
            </h1>
            <h3 v-if="is_nft" class="modal_label">
            <span> This is <span class="number">NFT</span> Collection Trophy.<br>
            <span class="number">{{ max_supply - minted }}</span> available.</span>
            </h3>
            <h3 class="modal_label">
                You are going to need Ambar:
            </h3>
            <div class="ambar_need">
                {{ ambar_need }} Ambar
                <img src="../../../../web/images/web/img/points/ambar.svg" alt="ambar">
            </div>
            <div class="point_current">
                You currently have <span class="number">{{ isEnoughBalanceCount }}/{{ ambar_need }}</span> Ambar
            </div>
            <h3 class="modal_label">
                You are going to need badges:
            </h3>
            <div class="trophies">
                <div class="trophies__item" v-for="badge of badgesNeeded">
                    <img class="trophies__badge-image"
                         :src="`storage/integrations/${badge.integration.name}/${badge.image}`"
                         :alt="badge.name">
                    <img class="trophies__hexagon" src="../../../../web/images/web/img/achievements/borders/border.svg"
                         alt="hexagon image">
                </div>
            </div>
            <div class="point_current">
                You currently have <span class="number">{{ userMatchingBadges }}/{{ badgesNeeded.length }}</span> badges
            </div>

            <div class="point_current mb-0">
                Choose wisely
            </div>
            <button-white
                :text="'Forge your trophy'"
                :disabled="isDisabledButton"
                class="validate_achievement_with_button"
                @click="showNextModal"
            >
            </button-white>
            <h4 class="modal_small_label">
                Claim your unique piece and showcase to the world in your trophy room
            </h4>
            <!--      <h4 class="modal_small_label number mb-0">-->
            <!--        *plus {{ receive_uru }} Ambar-->
            <!--      </h4>-->
            <!--            <button-white :text="'Forge your legacy'" class="validate_achievement_with_button"></button-white>-->
            <!--            <h4 class="modal_small_label">-->
            <!--                Mint a NFT with perks and preserve it forever your-->
            <!--            </h4>-->
            <!--            <h4 class="modal_small_label number mb-30">-->
            <!--                *plus mint and gas fee-->
            <!--            </h4>-->
            <!--            <h4 class="modal_small_label mb-0">-->
            <!--                Validation could take some time-->
            <!--            </h4>-->
        </div>
    </div>

    <div v-if="showModal && currentStep === 2" class="modal_background" @click.self="closeClaimTrophy">
        <Loader v-if="statusLoading"/>
        <div v-else class="modal_window modal_forge_trophy mb-10">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeClaimTrophy"
                 class="modal_close_button">
            <h1 class="modal_header">
                Forge your trophy
            </h1>
            <h3 class="modal_label">
                Choose what you want:
            </h3>
            <div class="trophy-list">
                <div :class="{ 'selected': activeTrophy == 3 }" class="trophy" @click="activeTrophy = 3">
                    <div class="icon-trophy">
                        <div class="trophies">
                            <div style="width: 44px; height: 44px; min-width: unset; min-height: unset"
                                 class="trophies__item">
                                <img class="trophies__badge-image"
                                     :src="`storage/integrations/${badgesNeeded[0].integration.name}/${badgesNeeded[0].image}`"
                                     :alt="badgesNeeded[0].name">
                                <img class="trophies__hexagon"
                                     src="../../../../web/images/web/img/achievements/borders/border.svg"
                                     alt="hexagon image">
                            </div>
                        </div>
                    </div>
                    <label class="number">
                        1
                    </label>
                    <label>
                        Trophy
                    </label>
                </div>
                <div :class="{ 'selected': activeTrophy == 2 }" class="trophy" @click="activeTrophy = 2">
                    <div class="icon-trophy">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="24" viewBox="0 0 26 24" fill="none">
                            <path
                                d="M21 8.3335C17.6667 3.00016 13 1.00016 13 1.00016C13 1.00016 8.33333 3.00016 5 8.3335C1.66667 13.6668 1 19.6668 1 19.6668C1 19.6668 6.33333 22.3335 13 22.3335C19.6667 22.3335 25 19.6668 25 19.6668C25 19.6668 24.3333 13.6668 21 8.3335Z"
                                stroke="#D4F7FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path
                                d="M17.4438 10.4585C15.5919 7.4585 12.9993 6.3335 12.9993 6.3335C12.9993 6.3335 10.4068 7.4585 8.5549 10.4585C6.70305 13.4585 6.33268 16.8335 6.33268 16.8335C6.33268 16.8335 9.29564 18.3335 12.9993 18.3335C16.7031 18.3335 19.666 16.8335 19.666 16.8335C19.666 16.8335 19.2956 13.4585 17.4438 10.4585Z"
                                stroke="#D4F7FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M1 19.6667L6.33333 17" stroke="#D4F7FF" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                            <path d="M19.666 17L24.9993 19.6667" stroke="#D4F7FF" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13 6.33333V1" stroke="#D4F7FF" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <label class="number">
                        {{ receive_uru }}
                    </label>
                    <label>
                        Uru
                    </label>
                </div>
                <div v-if="isNFTKey" :class="{ 'selected': activeTrophy == 1 }" class="trophy"
                     @click="activeTrophy = 1">
                    <div class="icon-trophy">
                        <img style="width: 44px; height: 44px; padding: 4px;" :src="getImageKey()" alt="close">
                    </div>
                    <label class="number">
                        1
                    </label>
                    <div class="tip">
                        {{ getImageName() }}
                        <!--                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none">
                                                    <path d="M8.76953 8.4375L8.80028 8.4225C8.89645 8.37445 9.00438 8.35497 9.11128 8.36637C9.21818 8.37776 9.31958 8.41955 9.40346 8.48678C9.48735 8.55402 9.55021 8.64388 9.58461 8.74574C9.619 8.8476 9.62349 8.95717 9.59753 9.0615L9.06653 11.1885C9.04039 11.2929 9.04473 11.4026 9.07905 11.5045C9.11337 11.6065 9.17621 11.6965 9.26013 11.7639C9.34406 11.8312 9.44553 11.8731 9.55253 11.8845C9.65952 11.8959 9.76754 11.8764 9.86378 11.8282L9.89453 11.8125M16.082 9C16.082 9.88642 15.9074 10.7642 15.5682 11.5831C15.229 12.4021 14.7318 13.1462 14.105 13.773C13.4782 14.3998 12.7341 14.897 11.9151 15.2362C11.0962 15.5754 10.2185 15.75 9.33203 15.75C8.44561 15.75 7.56787 15.5754 6.74892 15.2362C5.92997 14.897 5.18586 14.3998 4.55906 13.773C3.93226 13.1462 3.43506 12.4021 3.09584 11.5831C2.75663 10.7642 2.58203 9.88642 2.58203 9C2.58203 7.20979 3.29319 5.4929 4.55906 4.22703C5.82493 2.96116 7.54182 2.25 9.33203 2.25C11.1222 2.25 12.8391 2.96116 14.105 4.22703C15.3709 5.4929 16.082 7.20979 16.082 9ZM9.33203 6.1875H9.33803V6.1935H9.33203V6.1875Z" stroke="#18181B" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <div class="tip-block">

                                                </div>-->
                    </div>
                </div>
            </div>
            <button-white
                :text="'Choose'"
                :disabled="activeTrophy == 0"
                class="validate_achievement_with_button"
                @click="chooseTrophy"
            >
            </button-white>
        </div>
    </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
import api from "../../api/api.js";
import Web3 from "web3";
import Loader from "../Loader.vue";

export default {
    components: {
        Loader,
        buttonWhite,
        Web3
    },
    data() {
        return {
            showModal: false,
            statusLoading: true,
            buy_button_text: 'Buy for Ambars',
            social_proof_button_text: 'Validate achievement',
            proof_button_image: '../../../../web/images/web/img/social_icons/proof.svg',
            ambar_need: 0,
            receive_uru: 0,
            is_nft: false,
            minted: 0,
            max_supply: 0,
            badgesNeeded: [],
            userBadges: [],
            currentStep: 1,
            activeTrophy: 0,
            isNFTKey: false,
        }
    },
    methods: {
        check2afAuth() {
            api.get('/api/2fa-status').then(resp => {
                if (resp && resp.status === 200) {
                    if (resp.data.twoFAStatus === 0 && this.is_nft) {
                        store.state.notification = {
                            message: 'This action requires two-factor authentication!',
                            type: 'error',
                            show: true
                        }
                        store.state.modals.claimTrophyModal.show = false;
                    } else {
                        this.showModal = true;
                    }
                } else {
                    this.showModal = true;
                }
            }).catch(error => {
                console.log('api profile error: ', error)
            })
        },

        closeClaimTrophy() {
            store.state.modals.claimTrophyModal.show = false;
        },
        getBadges() {
            api.get('/api/badges').then(resp => {
                if (resp.status === 200) {
                    this.userBadges = resp.data.data;
                }
            })
        },
        showNextModal() {
            this.currentStep = 2;
        },
        getImageKey() {
            return `/storage/keys/${store.state.modals.claimTrophyModal.data.key?.image}`;
        },
        getImageName() {
            return store.state.modals.claimTrophyModal.data.key?.name;
        },
        chooseTrophy() {
            if (this.activeTrophy == 1) {
                this.forgeKey();
            } else if (this.activeTrophy == 2) {
                api.get(`/api/forge/${store.state.modals.claimTrophyModal.data.id}/getBalance`).then(resp => {
                    store.state.notification = {
                        message: resp.data.message,
                        type: 'success',
                        show: true
                    }
                });
                store.state.modals.claimTrophyModal.show = false;
                this.$router.push('/exchange');
            } else if (this.activeTrophy == 3) {
                this.forgeTrophy()
            }
        },
        async forgeKey() {
            console.log('forgeKEY')
            this.statusLoading = true;
            if (store.state.modals.claimTrophyModal.data.is_nft) {
                if (!store.state.userAddress) {
                    await this.$store.dispatch('initMetaMaskConnection', { nftType: 'key' });
                } else {
                    await this.$store.dispatch('initializeContract', { nftType: 'key' });
                }
                if (!store.state.userAddress) {
                    store.state.notification = {
                        message: 'First connect your wallet!',
                        type: 'warning',
                        show: true
                    }
                    return;
                }
                api.post(`/api/forge/vouchers/sign`, {
                    user_address: store.state.userAddress,
                    category_id: store.state.modals.claimTrophyModal.data.key.id,
                    nft_type: 'key'
                }).then(async resp => {
                    if (resp.status === 200) {
                        await this.mintNFT(resp.data, 'key')
                    } else {
                        store.state.notification = {
                            message: resp.data.message,
                            type: resp.data.type,
                            show: true
                        }
                    }
                })
            } else {
                api.get(`/api/chests/${store.state.modals.claimTrophyModal.data.id}/get`).then(resp => {
                    store.state.notification = {
                        message: resp.data.message,
                        type: 'success',
                        show: true
                    }
                });
                this.$router.push('/my-chests');
                this.statusLoading = false;
                store.state.modals.claimTrophyModal.show = false;
            }
        },
        async forgeTrophy() {
            this.statusLoading = true;
            if (store.state.modals.claimTrophyModal.data.is_nft) {
                if (!store.state.userAddress) {
                    await this.$store.dispatch('initMetaMaskConnection');
                } else {
                    await this.$store.dispatch('initializeContract');
                }
                if (!store.state.userAddress) {
                    store.state.notification = {
                        message: 'First connect your wallet!',
                        type: 'warning',
                        show: true
                    }
                    return;
                }
                api.post(`/api/forge/vouchers/sign`, {
                    user_address: store.state.userAddress,
                    category_id: store.state.modals.claimTrophyModal.data.id,
                    nft_type: 'trophy'
                }).then(async resp => {
                    if (resp.status === 200) {
                        await this.mintNFT(resp.data, 'trophy')
                    } else {
                        store.state.notification = {
                            message: resp.data.message,
                            type: resp.data.type,
                            show: true
                        }
                    }
                })
            } else {
                api.post(`/api/forge/claim/${store.state.modals.claimTrophyModal.data.id}`).then(resp => {
                    store.state.notification = {
                        message: resp.data.message,
                        type: resp.data.type,
                        show: true
                    }
                });
                this.statusLoading = false;
                store.state.modals.claimTrophyModal.show = false;
            }
        },
        async mintNFT(voucher, nftType = 'trophy') {
            const mintResult = await store.state.contract.methods.claimNFT(voucher)
                .send({from: store.state.userAddress, gasLimit: await this.estimateClaimGas(voucher)});
            store.state.contract.events.NFTMinted({
                filter: {newOwner: store.state.userAddress},
                fromBlock: mintResult.blockNumber,
            }).once('data', (event) => {
                if (nftType === 'trophy') {
                    api.post(`/api/forge/claim/${store.state.modals.claimTrophyModal.data.id}`, {
                        owner: store.state.userAddress,
                        token_id: event.returnValues.tokenId.toString(),
                    }).then(resp => {
                        store.state.notification = {
                            message: resp.data.message,
                            type: resp.data.type,
                            show: true
                        }
                    });
                    this.statusLoading = false;
                    store.state.modals.claimTrophyModal.show = false;
                } else if (nftType === 'key') {
                    console.log('event', event.returnValues.tokenId.toString())
                    api.post(`/api/chests/${store.state.modals.claimTrophyModal.data.id}/get`,{
                        owner: store.state.userAddress,
                        token_id: event.returnValues.tokenId.toString(),
                    }).then(resp => {
                        store.state.notification = {
                            message: resp.data.message,
                            type: 'success',
                            show: true
                        }
                    });
                    this.$router.push('/my-chests');
                    this.statusLoading = false;
                    store.state.modals.claimTrophyModal.show = false;
                }
            });
        },
        async estimateClaimGas(voucher) {
            let gas = BigInt(5000000)
            await store.state.contract.methods.claimNFT(voucher).estimateGas({from: store.state.userAddress})
                .then(function (gasAmount) {
                    gas = gasAmount
                })
                .catch(function (error) {
                    console.log(error);
                });
            return store.state.web3.utils.toHex(Number(BigInt(gas) * BigInt(2)))
        },
    },
    computed: {
        isEnoughBalanceCount() {
            return (store.state.user.balances.ambar >= this.ambar_need ? this.ambar_need : store.state.user.balances.ambar);
        },
        userMatchingBadges() {
            let arrNeeded = [];
            this.badgesNeeded.forEach(badge => {
                arrNeeded.push(badge.id)
            })
            let arrBadges = [];
            this.userBadges.forEach(badge => {
                arrBadges.push(badge.id)
            })
            let count = 0;
            arrBadges.forEach(badge => {
                if (arrNeeded.includes(badge)) {
                    count++
                }
            });
            return count;
        },
        isDisabledButton() {
            return !(this.ambar_need === this.isEnoughBalanceCount && this.userMatchingBadges === this.badgesNeeded.length);
        }
    },
    mounted() {
        this.ambar_need = Math.floor(store.state.modals.claimTrophyModal.data.price);
        this.receive_uru = Math.floor(store.state.modals.claimTrophyModal.data.receive);
        this.badgesNeeded = store.state.modals.claimTrophyModal.data.badges;
        this.is_nft = store.state.modals.claimTrophyModal.data.is_nft;
        this.check2afAuth();
        this.minted = store.state.modals.claimTrophyModal.data.minted;
        this.max_supply = store.state.modals.claimTrophyModal.data.max_supply;
        this.getBadges();
        this.statusLoading = false;
        if (store.state.modals.claimTrophyModal.data.key !== null && store.state.modals.claimTrophyModal.data.key.quantity > 0) {
            this.isNFTKey = true;
        }
    }

}
</script>

<style scoped>
.modal_window {
    padding: 40px 30px 60px 30px;
}

.main-button {
    margin-top: 24px;
    margin-bottom: 12px;
    border-radius: 2px;
    font-size: 18px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 20px;
}

.modal_sign_up_with_button {
    height: 40px;
}

.point_current {
    margin-top: 30px;
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.90);
    font-size: 20px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 28px;
}

.number {
    color: #CAFB01;
}

.ambar_need {
    display: flex;
    align-items: center;
    text-align: center;
    gap: 8px;
    color: #CAFB01;
    font-size: 16px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 22px;
    word-wrap: break-word
}

.trophies {
    display: flex;
    gap: 14px;
    flex-direction: row;
    flex-wrap: wrap;
}

.trophies__item {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    position: relative;
    align-items: center;
    justify-content: center;
}

.trophies__badge-image {
    width: 50%;
    margin-top: 5%;
}

.trophies__hexagon {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-30 {
    margin-bottom: 30px !important;
}

.modal_small_label {
    margin-bottom: 12px;
}

.trophy-list {
    margin-top: 30px;
    margin-bottom: 18px;
    display: flex;
    gap: 16px;

    .trophy {
        padding: 12px;
        justify-content: center;
        flex: 1 0 0;

        display: flex;
        flex-direction: column;
        color: var(--main, #CAFB01);
        text-align: center;
        align-items: center;

        border-radius: 3px;
        border: 0.5px solid var(--main, #CAFB01);
        background: rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(2.5px);

        /* H5 */
        font-family: 'Share Tech Mono', monospace;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 140%; /* 22.4px */

        .number {
            font-family: 'Share Tech Mono', monospace;
            font-size: 16px;
            font-style: normal;
            font-weight: 700;
            line-height: 140%; /* 22.4px */
        }

        .icon-trophy {
            border-radius: 11px;
            background: #131129;
            width: 44px;
            height: 44px;
            margin-bottom: 12px;

            svg {
                width: 100%;
                height: 100%;
                padding: 4px;
            }
        }
    }

    .trophy.selected {
        background: var(--main, #CAFB01);
        color: #18181B;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;

        .number {
            color: #18181B;
            font-family: 'Share Tech Mono', monospace;
            font-size: 16px;
            font-style: normal;
            font-weight: 700;
            line-height: 140%; /* 22.4px */
        }
    }

    .trophy:not(.selected):hover {
        cursor: pointer;
        background: rgba(255, 255, 255, 0.20);
    }
}

.tip {
    display: flex;
    align-items: center;
    gap: 4px
}

.tip:hover {
    cursor: pointer;
}

.tip-block {
    border-radius: 2px;
    background: #909090;
    width: 460px;
    height: 104px;
    position: absolute;
    top: -100%;
    left: calc(60px - 50%);
}

</style>
