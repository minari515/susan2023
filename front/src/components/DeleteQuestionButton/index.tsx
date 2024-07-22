// DeleteQuestionButton/index.tsx

import React from 'react';

const DeleteQuestionButton = () => {
  const handleClick = () => {
    alert('質問を削除しますか？');
  };

  return (
    <button onClick={handleClick}>
      質問を削除
    </button>
  );
};

export default DeleteQuestionButton;