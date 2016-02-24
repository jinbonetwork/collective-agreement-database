import React from 'react';

export default class ArticleView extends React.Component {
  render() {
    return (
      <div>
        ArticleView : {this.props.params.articleId}
        {this.props.children}
      </div>
    );
  }
}
