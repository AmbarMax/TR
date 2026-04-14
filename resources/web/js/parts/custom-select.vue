<template>
    <div :class="class" class="custom-select" :tabindex="tabindex" @blur="open = false">
        <div class="selected" :class="{ open: open }" @click="open = !open">
            {{ selected }}
        </div>
        <div class="items" :class="{ selectHide: !open }">
            <div
                v-for="(option, i) of options"
                :key="i"
                @click="
          selected = option;
          open = false;
          $emit('input', option);
        "
            >
                {{ option }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        options: {
            type: Array,
            required: true,
        },
        class: {
            type: String,
            default: '',
            required: false,
        },
        default: {
            type: String,
            required: false,
            default: null,
        },
        tabindex: {
            type: Number,
            required: false,
            default: 0,
        },
    },
    data() {
        return {
            selected: this.default
                ? this.default
                : this.options.length > 0
                    ? this.options[0]
                    : null,
            open: false,
        };
    },
    mounted() {
        this.$emit("input", this.selected);
    },
};
</script>

<style scoped>
.custom-select {
    position: relative;
    width: 100%;
    text-align: left;
    outline: none;
    height: 30px;
    line-height: 30px;
}

.select-exchange{
    height: 42px;
    .selected{
        width: 100%;
        height: 42px;
        background: rgba(186, 186, 186, 0.15);
        border-radius: 4px;
        padding: 10px 12px;
    }
    .selected:after {
        top: 18px;
    }
}

.custom-select .selected {
    width: 160px;
    height: 30px;
    background: rgba(186, 186, 186, 0.06);
    padding: 4px 10px 4px 14px;
    border: 1px rgba(186, 186, 186, 0.60) solid;
    justify-content: flex-start;
    align-items: center;
    cursor: pointer;
    user-select: none;

    color: rgba(255, 255, 255, 0.90);
    font-size: 16px;
    font-weight: 400;
    line-height: 20px;
}

.custom-select .selected:after {
    position: absolute;
    content: "";
    top: 13px;
    right: 1em;
    width: 0;
    height: 0;
    border: 5px solid transparent;
    border-color: #fff transparent transparent transparent ;
}

.custom-select .items {
    color: #fff;
    border-radius: 6px;
    overflow: hidden;
    position: absolute;
    background: rgba(255, 255, 255, 0.6);
    left: 0;
    right: 0;
    z-index: 1;
}

.custom-select .items div {
    color: #fff;
    padding-left: 1em;
    cursor: pointer;
    user-select: none;
}

.custom-select .items div:hover {
    background-color: rgba(255, 255, 255, 0.20);
}

.selectHide {
    display: none;
}

.profile_select .selected{
    background: rgba(186, 186, 186, 0.15)!important;
    border-radius: 4px;
    padding-top: 10px;
    height: 43px;
    max-width: unset!important;
    width: unset!important;
}

.profile_select .selected:after {
    position: absolute;
    top: 20px;
}

.profile_select .items {
    font-size: 16px;
    background-color: #303033;
    margin-top: 8px;
    border-radius: 4px;
    border: 1px rgba(186, 186, 186, 0.60) solid;
}

.profile_select .items div {
    margin: 4px 0;
    height: 34px;
    background-color: #303033;
    color: white;
    padding-left: 1em;
    cursor: pointer;
    user-select: none;
}

.profile_select .items div:hover {
    background: #59595c;
}

.w-200 .selected {
    width: 180px!important;
}

</style>
