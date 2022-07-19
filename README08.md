# 8-2 タグ入力のVueコンポーネントを表示する

## 1. Vue Tags Inputのインストール

+ `$ npm install -D @johmun/vue-tags-input@2.1.0`を実行<br>

## 2. タグ入力のVueコンポーネントを作成する

+ `$ touch resources/js/components/ArticleTagsInput.vue`を実行<br>

+ `server/resources/js/components/ArticleTagsInput.vue`を編集<br>

```vue:ArticleTagsInput.vue
<template>
  <div>
    <vue-tags-input
      v-model="tag"
      :tags="tags"
      :autocomplete-items="filteredItems"
      @tags-changed="(newTags) => (tags = newTags)"
    />
  </div>
</template>

<script>
import VueTagsInput from "@johmun/vue-tags-input";

export default {
  components: {
    VueTagsInput,
  },
  data() {
    return {
      tag: "",
      tags: [],
      autocompleteItems: [
        {
          text: "Spain",
        },
        {
          text: "France",
        },
        {
          text: "USA",
        },
        {
          text: "Germany",
        },
        {
          text: "China",
        },
      ],
    };
  },
  computed: {
    filteredItems() {
      return this.autocompleteItems.filter((i) => {
        return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
      });
    },
  },
};
</script>

<style lang="css" scoped>
.vue-tags-input {
  max-width: inherit;
}
</style>
<style lang="css">
.vue-tags-input .ti-tag {
  background: transparent;
  border: 1px solid #747373;
  color: #747373;
  margin-right: 4px;
  border-radius: 0px;
  font-size: 13px;
}
</style>
```

+ `server/resources/js/app.js`を編集<br>

```js:app.js
import './bootstrap'
import Vue from 'vue'
import ArticleLike from './components/ArticleLike'
import ArticleTagsInput from './components/ArticleTagsInput'

const app = new Vue({
  el: '#app',
  components: {
    ArticleLike,
    ArticleTagsInput,
  }
})
```

+ `server/resources/views/articles/form.blade.php`を編集<br>

```html:form.blade.php
@csrf
<div class="md-form">
    <label>タイトル</label>
    <input type="text" name="title" class="form-control" value="{{ $article->title ?? old('title') }}" required>
</div>
<!-- 追加 -->
<div class="form-group">
    <article-tags-input>
    </article-tags-input>
</div>
<!-- ここまで -->
<div class="form-group">
    <label></label>
    <textarea name="body" class="form-control" rows="16" placeholder="本文" required>{{ $article->body ?? old('body') }}</textarea>
</div>
```