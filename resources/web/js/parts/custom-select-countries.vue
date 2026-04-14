<template>
    <div class="custom-select" :tabindex="tabindex" @blur="open = false">
        <input style="width: 100%;"
            @click="open = !open"
            type="text"
            autocomplete="off"
            v-model="selectedName"
            @input="filterOptions"
            placeholder="Search..."
        />
        <div class="items" :class="{ selectHide: !open }">
            <div
                v-for="(option, i) of filteredOptions"
                :key="i"
                @click="
                  selected = option.id;
                  open = false;
                  $emit('input', option.id);
                "
            >
                {{ option.name }}
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
        id: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            selected: null,
            open: false,
            selectedName: '',
            filteredOptions: this.options,
        };
    },
    mounted() {
        setTimeout(() => {
            this.filterOptions();
            this.open = false;
        }, 500);
    },
    methods: {
        filterOptions() {
            if (this.selectedName.trim() === '') {
                this.filteredOptions = this.options;
            } else {
                this.open = true;
                this.filteredOptions = this.options.filter((option) =>
                    option.name.toLowerCase().includes(this.selectedName.toLowerCase())
                );
            }
        },
    },
    watch: {
        selected(newValue, oldValue) {
            this.selectedName = this.options.find(item => item.id === newValue).name;
            this.$emit("handleCountry", this.selected);
        },
        id(newValue, oldValue) {
            this.selected = newValue;
        }
    }
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
    max-height: 1000%;
    overflow-y: auto;

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
